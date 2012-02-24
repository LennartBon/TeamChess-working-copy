<?php

/**
 * Builds form page for entering a new match into the system
 * @param -- NONE --
 * @return form page as HTML
 */
function tch_new_match () {

	require_once 'HTML/Template/IT.php';

// 	define ('TCH_MAXTABLES', 16);
	
	global $wpdb, $table_prefix;
	global $tch_template_dir;

	$new_match = new HTML_Template_IT ($tch_template_dir);
	$new_match->loadTemplatefile ('new-match.tpl', true, true);

	$new_match->setCurrentBlock ('NONCEFIELD');
	$new_match->setVariable ('nonce_field',
							 wp_nonce_field ('new_match_add', 'new_match_nonce_field',
							 				 $referer = 'true', $echo = 'false'));
	$new_match->parseCurrentBlock ();

	$q = "SELECT max(id) FROM {$table_prefix}tch_matches";
	$lastgame = (int) $wpdb->get_var ($q);
		
	$q = "SELECT DISTINCT team FROM {$table_prefix}tch_matches ORDER BY team";
	$result1 = $wpdb->get_results ($q, ARRAY_A);

	foreach ((array) $result1 AS $row1) {
		$new_match->setCurrentBlock ('TEAMMENU');
		$new_match->setVariable ('team', tch_striptags ($row1['team']));
		$new_match->parseCurrentBlock ();
	}

	$teamsizes = array ();
	$teamsizes = explode (',', get_option ('tch_teamsizes'));
	$default_teamsize = (int) get_option ('tch_default_teamsize');

	foreach ((array) $teamsizes AS $teamsize) {
		$new_match->setCurrentBlock ('TEAMSIZEMENU');
		$new_match->setVariable ('teamsize', (int) $teamsize);
		if ($default_teamsize == $teamsize) {
			$new_match->setVariable ('selected', ' selected="selected"');
		} else {
			$new_match->setVariable ('selected', '');
		}
		$new_match->parseCurrentBlock ();
	}

	$q  = "SELECT firstname, lastname, id, max(match_id) AS latest";
	$q .= " FROM {$table_prefix}tch_players LEFT JOIN {$table_prefix}tch_tables";
	$q .= " ON {$table_prefix}tch_players.id = {$table_prefix}tch_tables.player_id";
	$q .= " GROUP BY id";
	$q .= " ORDER BY firstname ASC, lastname ASC";

	$result1 = $wpdb->get_results ($q, ARRAY_A);

	$pn          = 0;
	$player_name = array ();
	$player_no   = array ();
	
	foreach ((array) $result1 AS $row1) {
		$player_lastgame = (int) $row1['latest'];
		if ((0 == $player_lastgame) || ($lastgame - $player_lastgame < 100)) {
			$pn += 1;
			$player_name[$pn] = tch_striptags ($row1['firstname'] . ' ' . $row1['lastname']);
			$player_no[$pn]   = (int) $row1['id'];
		}
	}

	for ($tn = 1; $tn <= TCH_MAXTABLES; $tn++) {
		for ($i = 1; $i <= $pn; $i++) {
			$new_match->setCurrentBlock ('PLAYERMENU');
			$new_match->setVariable ('m', $i);
			$new_match->setVariable ('n', $tn);
			$new_match->setVariable ('id', $player_no[$i]);
			$new_match->setVariable ('name', $player_name[$i]);
			$new_match->parseCurrentBlock ();
		}

		$new_match->setCurrentBlock ('TABLEMENU');
		$new_match->setVariable ('n', $tn);
		$new_match->parseCurrentBlock ();
	}

	$s = $new_match->get ();
	return $s;
	
}

/**
 * Reads form data and enters a new match into the system
 * @param -- NONE -- (implicitly $_REQUEST)
 * @returns id of created match or -1 if error
 */
function tch_newmatch_add () {

	define ('INSERT_FAILED', -1);
	global $wpdb, $table_prefix;
	
	// if this fails, check_admin_referer() will automatically print a "failed" page and die.
	if (! check_admin_referer ('new_match_add', 'new_match_nonce_field')) {
	   // die, somehow
	}
	
	// Sanity check
	if ('' == $_REQUEST['matchdate'] or
		'' == $_REQUEST['opponent'] or
		'' == $_REQUEST['tables']) {
		return INSERT_FAILED;
	}
	
	$match_id = (int) $wpdb->get_var ("SELECT max(id) FROM {$table_prefix}tch_matches") + 1;

	$opponent  = tch_striptags ($_REQUEST['opponent']);
	$matchdate = tch_striptags ($_REQUEST['matchdate']);
	$league    = tch_striptags ($_REQUEST['league']);

	$team      = tch_striptags ($_REQUEST['team']);
	if ('otherTeam' == $team) {
		$team = tch_striptags ($_REQUEST['newteam']);
	}
	
	$teamsize  = tch_striptags ($_REQUEST['tables']);
	if ('otherSize' == $teamsize) {
		$teamsize = (int) tch_striptags ($_REQUEST['newteamsize']);
	} else {
		$teamsize = (int) $teamsize;
	}
	if (0 >= $teamsize) {
		return INSERT_FAILED;
	}
	
	$q  = "INSERT INTO {$table_prefix}tch_matches";
	$q .= " (id, opponent, matchdate, league, team)";
	$q .= " VALUES ('%d', '%s', '%s', '%s', '%s')";

	$num_rows = $wpdb->query (
					$wpdb->prepare ($q, $match_id, $opponent, $matchdate, $league, $team));
	if (FALSE === $num_rows) {
		// MySQL error
		return INSERT_FAILED;
	} else if (1 != $num_rows) {
		// Insertion error
		return INSERT_FAILED;
	}

	$q  = "INSERT INTO {$table_prefix}tch_tables";
	$q .= " (match_id, table_no, result, player_id)";
	$q .= " VALUES";

	for ($i = 1; $i <= $teamsize; $i++) {
		$result    = (int) $_REQUEST["Result{$i}"];
		$player_id = (int) $_REQUEST["Table{$i}"];
		$signature = tch_striptags ($_REQUEST["Signature{$i}"]);
		
		if ('' != $signature) {				// If a signature is given, use it to pick up player's id
			$q1 = "SELECT id FROM {$table_prefix}tch_players WHERE signature = '%s'";
			$player_id = (int) $wpdb->get_var ($wpdb->prepare ($q1, $signature));
			if (! $player_id)
				continue;					// Nonexistant signature, skip to next player
		}

		$q .= " ('{$match_id}', '{$i}', '{$result}', '{$player_id}')";
		if ($i < $teamsize)					// Do we have more tables coming?
			$q .= ",";						// ... Yes
	}

	$num_rows = $wpdb->query ($q);
	if (FALSE === $num_rows) {
		// MySQL error
		return INSERT_FAILED;
	} else if ($num_rows <= 0) {
		// Insertion error
		return INSERT_FAILED;
	} else {
		// Things should be OK
		return $match_id;
	}
}
	
?>
