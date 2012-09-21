<?php

/**
 * Builds form page for entering a new player into the system
 * @param -- NONE --
 * @return form page as HTML
 */
function tch_new_player () {

	require_once 'HTML/Template/IT.php';

	global $wpdb;
	global $tch_template_dir;

	$new_player = new HTML_Template_IT ($tch_template_dir);
	$new_player->loadTemplatefile ('new-player.tpl', true, true);

	$new_player->setCurrentBlock ('NONCEFIELD');
	$new_player->setVariable ('nonce_field',
							  wp_nonce_field ('new_player_add', 'new_player_nonce_field',
											  $referer = 'true', $echo = 'false'));
	$new_player->parseCurrentBlock ();

	$s = $new_player->get ();
	return $s;
	
}

/**
 * Reads form data and enters a new player into the system
 * @param -- NONE -- (implicitly $_REQUEST)
 * @returns id of created player or -1 if error
 */
function tch_newplayer_add () {

	define ('INSERT_FAILED', -1);
	global $wpdb, $table_prefix;
	
	// if this fails, check_admin_referer() will automatically print a "failed" page and die.
	if (! check_admin_referer ('new_player_add', 'new_player_nonce_field')) {
	   // die, somehow
	}
	
	// Sanity check
	if ('' == $_REQUEST['firstname'] or
		'' == $_REQUEST['lastname']) {
		return INSERT_FAILED;
	}
	
	$firstname  = tch_striptags ($_REQUEST['firstname']);
	$lastname   = tch_striptags ($_REQUEST['lastname']);
	$signature  = tch_striptags ($_REQUEST['signature']);
	$ssf_id     = (int) $_REQUEST['ssfid'];
	$fide_id    = (int) $_REQUEST['fideid'];
	$info       = tch_striptags ($_REQUEST['playerinfo']);
	if (isset ($_REQUEST['primarymember'])) {
		$primary = (1 == $_REQUEST['primarymember']);
	} else {
		$primary = false;
	}
	
	/** The two ID parameters are optional.   */
	/* This means some juggling is necessary  */
	/* to get the INSERT statement right.     */
	$id_columns = "";
	$id_values  = "";
	if ($ssf_id > 0) {
		$id_columns .= ", ssf_id";
		$id_values  .= ", '%d'";
	}
	if ($fide_id > 0) {
		$id_columns .= ", fide_id";
		$id_values  .= ", '%d'";
	}
	
	$q  = "INSERT INTO {$table_prefix}tch_players";
	$q .= " (firstname, lastname, signature, primary_member, player_info" . $id_columns . ")";
	$q .= " VALUES ('%s', '%s', '%s', '%s', '%s'" . $id_values . ")";
	
	$q_prepared = "";
	if (($ssf_id > 0) && ($fide_id > 0)) {
		$q_prepared = $wpdb->prepare ($q, $firstname, $lastname, $signature,
									  $primary, $info, $ssf_id, $fide_id);
	} else if ($ssf_id > 0) {
		$q_prepared = $wpdb->prepare ($q, $firstname, $lastname, $signature,
									  $primary, $info, $ssf_id);
	} else if ($fide_id > 0) {
		$q_prepared = $wpdb->prepare ($q, $firstname, $lastname, $signature,
									  $primary, $info, $fide_id);
	} else {
		$q_prepared = $wpdb->prepare ($q, $firstname, $lastname, $signature,
									  $primary, $info);
	}

	$num_rows = $wpdb->query ($q_prepared);

	if (FALSE === $num_rows) {
		// MySQL error
		return INSERT_FAILED;
	} else if (1 != $num_rows) {
		// Insertion error
		return INSERT_FAILED;
	} else {
		// Things should be OK
		return $wpdb->insert_id;
	}
}

?>
