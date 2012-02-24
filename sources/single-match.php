<?php

/**
 * Builds a list whith all data for a particular match
 * @param id -> id of requested match
 * @return list as HTML table
 */
function tch_single_match ($atts, $content = null) {

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

	$single_match = new HTML_Template_IT ($tch_template_dir);
	$single_match->loadTemplatefile ('single-match.tpl', true, true);
	
	$q = "SELECT opponent, team, league, matchdate FROM {$table_prefix}tch_matches WHERE id = '%d'";
	$row1 = $wpdb->get_row ($wpdb->prepare ($q, $id), ARRAY_A);
	
	// Did we find exactly one match?
	if (1 != $wpdb->num_rows) {
		return '';
	}

	$q  = "SELECT table_no, player_id, result, firstname, lastname";
	$q .= " FROM {$table_prefix}tch_tables, {$table_prefix}tch_players";
	$q .= " WHERE match_id = '%d' AND player_id = {$table_prefix}tch_players.id";
	$q .= " ORDER BY table_no";

	$result2 = $wpdb->get_results ($wpdb->prepare ($q, $id), ARRAY_A);
	$number_of_tables = $wpdb->num_rows;
	
	$date = $row1['matchdate'];

	$single_match->setCurrentBlock ('HEADER');
	$single_match->setVariable ('num', $id);
	$single_match->setVariable ('team', tch_striptags ($row1['team'], TCH_SAFETAGS));
	$single_match->setVariable ('opponent', tch_striptags ($row1['opponent'], TCH_SAFETAGS));
	$single_match->setVariable ('league', tch_striptags ($row1['league'], TCH_SAFETAGS));
	$single_match->setVariable ('date', tch_striptags ($date, TCH_NOTAGS));
	$single_match->parseCurrentBlock ();

	$single_match->setCurrentBlock ('LINKS');
	if (is_singular()) {						// Links only in pages, not posts
		$single_match->setVariable ('style', '');
		$single_match->setVariable ('list_url', $tch_listpage_url);
		$single_match->setVariable ('prev', $id-1);
		$single_match->setVariable ('next', $id+1);
	}
	else {
		$single_match->setVariable ('style', ' style="display:none"');	// Hide
	}
	$single_match->parseCurrentBlock ();

	$result_sum = 0;
	$ratingsum = 0;
	$number_of_ratings = 0;
	
	$q  = "SELECT id FROM {$table_prefix}tch_ratingperiod";
	$q .= " WHERE ratingdate <= '%s' ORDER BY id DESC LIMIT 1";
	$period = (int) $wpdb->get_var ($wpdb->prepare ($q, $date));

	$odd_even = 1;
	
	foreach ((array) $result2 AS $row2) {
		$odd_even = ($odd_even + 1) % 2;

		$result = intval ($row2['result'])/2;
		$result_sum += $result;
		
		$player_id = intval ($row2['player_id']);
		
		$q  = "SELECT rating FROM {$table_prefix}tch_rating";
		$q .= " WHERE player_id = '%d' AND period_id = '%d'";
		$rating = (int) $wpdb->get_var ($wpdb->prepare ($q, $player_id, $period));
		
		if ($rating && (0 < $rating)) {
			$ratingsum += $rating;
			$number_of_ratings += 1;
		} else {
			$rating = '--';
		}
		
		$single_match->setCurrentBlock ('TABLE');
		$single_match->setVariable ('odd_even', $odd_even);
		$single_match->setVariable ('table', intval ($row2['table_no']));
		$single_match->setVariable ('list_url', $tch_listpage_url);
		$single_match->setVariable ('id', $player_id);
		$single_match->setVariable ('player',
								tch_striptags ($row2['firstname'].' '.$row2['lastname'], TCH_SAFETAGS));
		$single_match->setVariable ('rating', $rating);
		$single_match->setVariable ('result', $result);
		$single_match->parseCurrentBlock ();
	}
	
	$result_string = $result_sum . ' - ' . ($number_of_tables - $result_sum);
	if (0 < $number_of_ratings) {
		$average_rating = round ($ratingsum/$number_of_ratings, 0);
		$average_string = '(' . $average_rating . ')';
	} else {
		$average_string = '--';
	}
	
	$single_match->setCurrentBlock ('SUMMARY');
	$single_match->setVariable ('rating_average', $average_string);
	$single_match->setVariable ('team_result', $result_string);
	$single_match->parseCurrentBlock ();
	
	$s = $single_match->get();
	
	return $s;
}

?>
