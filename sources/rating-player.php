<?php

/**
 * Builds a list with historical rating for a particular player
 * @param id -> player ID within the database
 * @return list as HTML table
 */
function tch_rating_player ($atts, $content = null) {

	require_once 'HTML/Template/IT.php';
	 
	global $wpdb, $table_prefix;
	global $tch_template_dir;

	if ((is_null ($content)) || ($content == '')) {
		extract (shortcode_atts (array ('id' => '0'), $atts));
	} else {
		$id = $content;
	}
	 
	$id = intval ($id);

	$rating_player = new HTML_Template_IT ($tch_template_dir);
	$rating_player->loadTemplatefile ('rating-player.tpl', true, true);
	
	$q = "SELECT firstname, lastname FROM {$table_prefix}tch_players WHERE id = '%d'";
	
	$row1 = $wpdb->get_row ($wpdb->prepare ($q, $id), ARRAY_A);
	$name = $row1['firstname'] . ' ' . $row1['lastname'];
	
	$q  = "SELECT max(rating) FROM {$table_prefix}tch_rating WHERE player_id = '%d'";
	
	$max_rating = (int) $wpdb->get_var ($wpdb->prepare ($q, $id));

	$q  = "SELECT DISTINCT ratingdate, rating";
	$q .= " FROM {$table_prefix}tch_rating, {$table_prefix}tch_ratingperiod";
	$q .= " WHERE player_id = '%d'";
	$q .= " AND {$table_prefix}tch_rating.period_id = {$table_prefix}tch_ratingperiod.id";
	$q .= " ORDER BY ratingdate DESC";
	
	$result2 = $wpdb->get_results ($wpdb->prepare ($q, $id), ARRAY_A);
	
	$rating_player->setCurrentBlock ('HEADER');
	$rating_player->setVariable ('name', tch_striptags ($name, TCH_SAFETAGS));
	$rating_player->setVariable ('max', $max_rating);
	$rating_player->parseCurrentBlock ();
	
	$odd_even = 1;
	
	foreach ((array) $result2 AS $row2) {
		$odd_even = ($odd_even + 1) % 2;

		$rating_player->setCurrentBlock ('LIST');
		$rating_player->setVariable ('odd_even', $odd_even);
		$rating_player->setVariable ('date', tch_striptags ($row2['ratingdate'], TCH_NOTAGS));
		$rating_player->setVariable ('rating', intval ($row2['rating']));
		$rating_player->parseCurrentBlock ();
	}

	$s = $rating_player->get ();
	
	return $s;
}

?>
