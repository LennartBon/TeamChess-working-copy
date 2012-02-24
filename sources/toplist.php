<?php

/**
 * Builds various top lists from the database, e.g. most matches played
 * @param kind -> what kind of toplist is requested
 * @return list as HTML table
 */
function tch_toplist ($atts, $content = null) {

	require_once 'HTML/Template/IT.php';

	global $wpdb, $table_prefix;
	global $tch_template_dir;
	global $tch_listpage_url;
	
	if ((is_null ($content)) || ($content == '')) {
		extract (shortcode_atts (array ('kind' => ''), $atts));
	} else {
		$kind = $content;
	}
	 
	if (! isset ($kind))
		return '';									// No default for kind

	$toplist = new HTML_Template_IT ($tch_template_dir);
	
	switch ($kind) {
		case 'matches':
			$toplist->loadTemplatefile ('matches.tpl', true, true);
			
			$q  = "SELECT {$table_prefix}tch_tables.player_id, count(*) AS numgames,";
			$q .= " min({$table_prefix}tch_tables.match_id) AS firstgame,";
			$q .= " max({$table_prefix}tch_tables.match_id) AS lastgame,";
			$q .= " firstname, lastname";
			$q .= " FROM {$table_prefix}tch_tables, {$table_prefix}tch_players";
			$q .= " WHERE {$table_prefix}tch_tables.player_id = {$table_prefix}tch_players.id";
			$q .= " GROUP BY {$table_prefix}tch_tables.player_id";
			$q .= " ORDER BY numgames DESC, lastgame ASC";
			break;
		case 'points':
			$toplist->loadTemplatefile ('points.tpl', true, true);
			
			$q  = "SELECT {$table_prefix}tch_tables.player_id,";
			$q .= " count(*) AS numgames, sum(result) AS sumpoints,";
			$q .= " min({$table_prefix}tch_tables.match_id) AS firstgame,";
			$q .= " max({$table_prefix}tch_tables.match_id) AS lastgame,";
			$q .= " firstname, lastname";
			$q .= " FROM {$table_prefix}tch_tables, {$table_prefix}tch_players";
			$q .= " WHERE {$table_prefix}tch_tables.player_id = {$table_prefix}tch_players.id";
			$q .= " GROUP BY {$table_prefix}tch_tables.player_id";
			$q .= " HAVING sumpoints > 0";
			$q .= " ORDER BY sumpoints DESC, numgames ASC";
			break;
		case 'percent':
			$toplist->loadTemplatefile ('percent.tpl', true, true);
			
			$q  = "SELECT {$table_prefix}tch_tables.player_id,";
			$q .= " count(*) AS numgames, sum(result) AS sumpoints,";
			$q .= " sum(result)/(2.0*count(*)) AS pct,";
			$q .= " min({$table_prefix}tch_tables.match_id) AS firstgame,";
			$q .= " max({$table_prefix}tch_tables.match_id) AS lastgame,";
			$q .= " firstname, lastname";
			$q .= " FROM {$table_prefix}tch_tables, {$table_prefix}tch_players";
			$q .= " WHERE {$table_prefix}tch_tables.player_id = {$table_prefix}tch_players.id";
			$q .= " GROUP BY {$table_prefix}tch_tables.player_id";
			$q .= " HAVING numgames > 5 AND pct >= 0.30";
			$q .= " ORDER BY pct DESC, numgames DESC";
			break;
		default:
			return '';
	}

	$result1 = $wpdb->get_results ($q, ARRAY_A);
	
	$plac = 0;
	$odd_even = 1;
	
	foreach ((array) $result1 AS $row1) {
		$odd_even = ($odd_even + 1) % 2;
		$points = intval ($row1['sumpoints']);
		$plac += 1;
		
		$n1 = intval ($row1['firstgame']);
		$n2 = intval ($row1['lastgame']);
		$q = "SELECT matchdate FROM {$table_prefix}tch_matches WHERE id = '%d' OR id = '%d'";
		
		$result2 = $wpdb->get_results ($wpdb->prepare ($q, $n1, $n2), ARRAY_A);
		
		switch ($wpdb->num_rows) {
			case 1:
				$year_1 = substr (tch_striptags ($result2[0]['matchdate'], TCH_NOTAGS), 0, 4);
				$year_2 = $year_1;
				break;
			case 2:
				$year_1 = substr (tch_striptags ($result2[0]['matchdate'], TCH_NOTAGS), 0, 4);
				$year_2 = substr (tch_striptags ($result2[1]['matchdate'], TCH_NOTAGS), 0, 4);
				break;
			default:
				$year_1 = 0;
				$year_2 = 0;
				break;
		}

		$toplist->setCurrentBlock ('NAME');
		$toplist->setVariable ('odd_even', $odd_even);
		$toplist->setVariable ('place', $plac);
		$toplist->setVariable ('list_url', $tch_listpage_url);
		$toplist->setVariable ('id', intval ($row1['player_id']));
		$toplist->setVariable ('player', tch_striptags ($row1['firstname'].' '.$row1['lastname'], TCH_SAFETAGS));
		$toplist->setVariable ('number_of_matches', intval ($row1['numgames']));
		
		switch ($kind) {
			case 'matches':
				break;
			case 'percent':
				$toplist->setVariable ('percent', sprintf ("%.1f", 100.0*$row1['pct']));
				/* break; */			// Fall through
			case 'points':
				$toplist->setVariable ('number_of_points', sprintf ("%.1f", $points/2));
				break;
			default:
				break;
		}
		
		$toplist->setVariable ('start', $year_1);
		if ($year_1 == $year_2)	{			// All matches in same year
			$toplist->setVariable ('dash', '');
			$toplist->setVariable ('stop', '');
		} else {
			$toplist->setVariable ('dash', '&ndash;');
			$toplist->setVariable ('stop', $year_2);
		}
		$toplist->parseCurrentBlock ();
	}

	$s = $toplist->get ();

	return $s;
}

?>
