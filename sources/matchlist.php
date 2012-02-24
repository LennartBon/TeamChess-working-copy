<?php

/**
 * Builds a list of all matches in the database
 * @param --NONE--
 * @return list as HTML table
 */
function tch_matchlist (/* $atts, $content = null */) {

	require_once 'HTML/Template/IT.php';

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
	*/

	$matchlist = new HTML_Template_IT ($tch_template_dir);
	$matchlist->loadTemplatefile ('matchlist.tpl', true, true);
	
	$q  = "SELECT {$table_prefix}tch_matches.*,";
	$q .= " count(result) AS numgames, sum(result)/2 AS resultsum";
	$q .= " FROM {$table_prefix}tch_matches, {$table_prefix}tch_tables";
	$q .= " WHERE {$table_prefix}tch_matches.id = {$table_prefix}tch_tables.match_id";
	$q .= " GROUP BY id";
	$q .= " ORDER BY matchdate DESC, team DESC, id DESC";

	$result1 = $wpdb->get_results ($q, ARRAY_A);
	
	$odd_even = 1;

	foreach ((array) $result1 AS $row1) {
		$odd_even = ($odd_even + 1) % 2;
		
		$our_result = $row1['resultsum'] + 0;						// ** Hack! **
		$opp_result = $row1['numgames'] - $our_result;
		$res_string = $our_result . '&nbsp;&ndash;&nbsp;' . $opp_result;
	
		$matchlist->setCurrentBlock ('MATCH');
		$matchlist->setVariable ('odd_even', $odd_even);
		$matchlist->setVariable ('list_url', $tch_listpage_url);
		$matchlist->setVariable ('id', intval ($row1['id']));
		$matchlist->setVariable ('opponent', tch_striptags ($row1['opponent'], TCH_SAFETAGS));
		$matchlist->setVariable ('date', tch_striptags ($row1['matchdate'], TCH_NOTAGS));
		$matchlist->setVariable ('result', tch_striptags ($res_string, TCH_NOTAGS));
		$matchlist->setVariable ('league', tch_striptags ($row1['league'], TCH_SAFETAGS));
		$matchlist->setVariable ('team', tch_striptags ($row1['team'], TCH_SAFETAGS));
		$matchlist->parseCurrentBlock ();
	}

	$s = $matchlist->get ();
	
	return $s;
}

?>
