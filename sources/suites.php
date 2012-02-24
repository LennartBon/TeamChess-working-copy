<?php

/**
 * Builds lists with longest suites (wins, draws, no losses) in database.
 * @param -- NONE --
 * @return list as HTML table
 */
function tch_suites ($atts, $content = null) {

	require_once 'HTML/Template/IT.php';
	include 'class-suite.php';
	 
	global $wpdb, $table_prefix;
	global $tch_template_dir;
	global $tch_listpage_url;

	/*
	if ((is_null ($content)) || ($content == '')) {
	     extract (shortcode_atts (array ('id' => '0'), $atts));
	}
	else {
	     $id = $content;
	}
	 
	$id = intval ($id);				// Safe programming :)
	*/

	$q  = "CREATE TEMPORARY TABLE temp_suites";
	$q .= " (kind char(16), player int(6), name char(150),";
	$q .= "  f_match int(5), f_date date, s_match int(5), s_date date,";
	$q .= "  length int(4), points int(4), open bool)";

	$result1 = $wpdb->query ($q);

	$q  = "SELECT match_id, player_id, result, firstname, lastname, matchdate";
	$q .= " FROM {$table_prefix}tch_tables, {$table_prefix}tch_players, {$table_prefix}tch_matches";
	$q .= " WHERE player_id = {$table_prefix}tch_players.id";
	$q .= " AND match_id = {$table_prefix}tch_matches.id";
	$q .= " ORDER BY player_id ASC, match_id ASC";

	$result1 = $wpdb->get_results ($q, ARRAY_A);
	
	$last_player = 0;

	foreach ((array) $result1 AS $row1) {
		$s_id = intval ($row1['player_id']);

		if ($s_id != $last_player) {				// New player
			if (isset ($wins))
				$wins->close_suite (true);
			if (isset ($draws))
				$draws->close_suite  (true);
			if (isset ($no_losses))
				$no_losses->close_suite (true);

			$name = tch_striptags ($row1['firstname'] . ' ' . $row1['lastname'], TCH_SAFETAGS);

			$wins = new suite ('Wins', $s_id, $name, $wpdb);
			$draws = new suite ('Draws', $s_id, $name, $wpdb);
			$no_losses = new suite ('Nolosses', $s_id, $name, $wpdb);
		
			$last_player = $s_id;
		}

		$result = (int) $row1['result'];
		$match_num = (int) $row1['match_id'];
		$day = tch_striptags ($row1['matchdate']);

		switch ($result) {
			case 0:									// Loss, close all suites
				$wins->close_suite (false);
				$draws->close_suite  (false);
				$no_losses->close_suite (false);
				break;
			case 1:									// A draw
				$wins->close_suite (false);
				$draws->append_suite  ($match_num, $day, $result);
				$no_losses->append_suite ($match_num, $day, $result);
				break;
			case 2:									// A win
				$wins->append_suite ($match_num, $day, $result);
				$draws->close_suite  (false);
				$no_losses->append_suite ($match_num, $day, $result);
				break;
			default:								// Mysterious error
				echo sprintf (__("Unknown result: %s"), $result);
				break;
		}
	}
 
	$suite_list = new HTML_Template_IT ($tch_template_dir);
	$suite_list->loadTemplatefile ('suites.tpl', true, true);
	
	for ($i = 1; $i <= 3; $i++) {
		switch ($i) {
			case 1:
				$q  = "SELECT * FROM temp_suites";
				$q .= " WHERE kind = 'Wins' AND length >= 5";
				$q .= " ORDER BY length DESC, s_match ASC";
				$blknamn = "WIN";
				break;
			case 2:
				$q  = "SELECT * FROM temp_suites";
				$q .= " WHERE kind = 'Draws' AND length >= 4";
				$q .= " ORDER BY length DESC, s_match ASC";
				$blknamn = "DRAW";
				break;
			case 3:
				$q  = "SELECT * FROM temp_suites";
				$q .= " WHERE kind = 'Nolosses' AND length >= 10";
				$q .= " ORDER BY length DESC, points DESC, s_match ASC";
				$blknamn = "NOLOSS";
				break;
			default:
				break;
		}

		$result1 = $wpdb->get_results ($q, ARRAY_A);
		if (0 == $wpdb->num_rows)
			continue;

		$plac = 0;
		$odd_even = 1;

		foreach ((array) $result1 AS $row1) {
			$odd_even = ($odd_even + 1) % 2;
			$plac++;
			$mi = $row1['f_match'] . ' - ' . $row1['s_match'];
			$ln = $row1['length'];
			if ($row1['open'])
				$ln .= '*';
			$pt = $row1['points']/2;
			
			$suite_list->setCurrentBlock ($blknamn);
			$suite_list->setVariable ('odd_even', $odd_even);
			$suite_list->setVariable ('place', $plac);
			$suite_list->setVariable ('list_url', $tch_listpage_url);
			$suite_list->setVariable ('id', $row1['player']);
			$suite_list->setVariable ('name', $row1['name']);
			$suite_list->setVariable ('length', $ln);
			$suite_list->setVariable ('match_interval', $mi);
			$suite_list->setVariable ('number_of_points', $pt);
			$suite_list->parseCurrentBlock ();
		}
	}
	
	$s = $suite_list->get ();

	return $s;
}

?>
