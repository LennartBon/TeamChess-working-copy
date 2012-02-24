<?php

/**
 * Builds a list with all matches where a particular player participated 
 * @param id -> player ID within the database
 * @return list as HTML table
 */
function tch_player ($atts, $content = null) {

	require_once 'HTML/Template/IT.php';

	global $wpdb, $table_prefix;
	global $tch_template_dir;
	global $tch_listpage_url;

	if ((is_null ($content)) || ($content == '')) {
	     extract (shortcode_atts (array ('id' => '0'), $atts));
	} else {
	     $id = $content;
	}
	 
	$id = intval ($id);

	$player = new HTML_Template_IT ($tch_template_dir);
	$player->loadTemplatefile ('player.tpl', true, true);
	
	$q = "SELECT firstname, lastname FROM {$table_prefix}tch_players WHERE id = '%d'";
	$row2 = $wpdb->get_row ($wpdb->prepare ($q, $id), ARRAY_A);

	// Found a player?
	if (! $row2) {
		return '';
	}
	
	$q  = "SELECT match_id, table_no, result, matchdate, opponent, league, team";
	$q .= " FROM {$table_prefix}tch_tables, {$table_prefix}tch_matches";
	$q .= " WHERE player_id = '%d'";
	$q .= " AND {$table_prefix}tch_matches.id = {$table_prefix}tch_tables.match_id";
	$q .= " ORDER BY {$table_prefix}tch_tables.match_id ASC";

	$result1 = $wpdb->get_results ($wpdb->prepare ($q, $id), ARRAY_A);
	$number_of_matches = $wpdb->num_rows;
	
	$player->setCurrentBlock ('HEADER');
	$player->setVariable ('name', tch_striptags ($row2['firstname'].' '.$row2['lastname'], TCH_SAFETAGS));
	// $player->parseCurrentBlock ();  --  fixed at end
	
	$number_of_points = 0;
	$odd_even = 1;
	
	foreach ((array) $result1 AS $row1) {
		$number_of_points += intval ($row1['result']) / 2;
		$odd_even = ($odd_even + 1) % 2;

		$player->setCurrentBlock ('MATCH');
		$player->setVariable ('odd_even', $odd_even);
 		$player->setVariable ('list_url', $tch_listpage_url);
		$player->setVariable ('id', intval ($row1['match_id']));
		$player->setVariable ('date', tch_striptags ($row1['matchdate'], TCH_NOTAGS));
		$player->setVariable ('opponent', tch_striptags ($row1['opponent'], TCH_SAFETAGS));
		$player->setVariable ('league', tch_striptags ($row1['league'], TCH_SAFETAGS));
		$player->setVariable ('team', tch_striptags ($row1['team'], TCH_SAFETAGS));
		$player->setVariable ('table', intval ($row1['table_no']));
		$player->setVariable ('result', intval ($row1['result'])/2);
		$player->parseCurrentBlock ();
	}

	if ($number_of_matches > 0) {
		$percent_string = sprintf ("%.1f", ($number_of_points/$number_of_matches)*100);
	} else {
		$percent_string = '0';
	}

	$player->setCurrentBlock ('HEADER');
	$player->setVariable ('p', $number_of_points);
	$player->setVariable ('m', $number_of_matches);
	$player->setVariable ('percent', $percent_string);
	$player->parseCurrentBlock ();

	$player->setCurrentBlock ('SUMMARY');
	$player->setVariable ('p', $number_of_points);
	$player->setVariable ('m', $number_of_matches);
	$player->setVariable ('percent', $percent_string);
	$player->parseCurrentBlock ();

	$s = $player->get ();
	
	return $s;
}

?>
