<?php

/**
 * Builds list of most matches played in a particular season
 * @param season -> which season; defaults to current season
 *                 ('2011' is interpreted as '2011/12' etc)
 * @param league -> which league should be tabulated; defaults to all
 * @return list as HTML table
 */
function tch_season_toplist ($atts, $content = null) {

	require_once 'HTML/Template/IT.php';

	global $wpdb, $table_prefix;
	global $tch_template_dir;
	global $tch_listpage_url;
	
	if ((is_null ($content)) || ($content == '')) {
		extract (shortcode_atts (array ('season' => '', 'league' => ''), $atts));
	} else {
		return $content;					// No attempt at parsing, just give up (for now)
	}
	 
	if (empty ($season)) {
		$today = getdate ();
		$season = $today['year'];			// default to current season
		if ($today['mon'] <= 8)
			$season -= 1;					// **HACK** new season from September 1
	} else {
		$season = intval ($season);
	}
	$season_display = $season . '/' . (($season+1)%100);	// e.g. '2011' becomes '2011/12'
	
	if (empty ($league)) {
		// '' defaults to all leagues
		$league_display = __('all teams', 'teamchess');
	} else {
		$league_display = $league;
	}

	$season_toplist = new HTML_Template_IT ($tch_template_dir);
	$season_toplist->loadTemplatefile ('season-toplist.tpl', true, true);
	
	$season_startdate = $season . '-09-01';
	$season_stopdate  = ($season+1) . '-08-31';
	
	$q  = "SELECT firstname, lastname, player_id,";
	$q .= " COUNT(*) AS numgames, SUM(result) AS sumpoints";
	$q .= " FROM {$table_prefix}tch_tables, {$table_prefix}tch_matches, {$table_prefix}tch_players";
	$q .= " WHERE (matchdate >= %s) AND (matchdate <= %s)";
	if (! empty ($league))
		$q .= " AND (league = %s)";
	$q .= " AND {$table_prefix}tch_tables.match_id = {$table_prefix}tch_matches.id";
	$q .= " AND {$table_prefix}tch_tables.player_id = {$table_prefix}tch_players.id";
	$q .= " GROUP BY player_id";
	$q .= " HAVING numgames > 0";
	$q .= " ORDER BY numgames DESC, sumpoints DESC;";

	$result1 = $wpdb->get_results ($wpdb->prepare ($q, $season_startdate, $season_stopdate, $league), ARRAY_A);
	
	$season_toplist->setCurrentBlock ('HEADER');
	$season_toplist->setVariable ('season', $season_display);
	$season_toplist->setVariable ('league', tch_striptags ($league_display));
	$season_toplist->parseCurrentBlock ();
	
	$plac = 0;
	$odd_even = 1;
	
	foreach ((array) $result1 AS $row1) {
		$odd_even = ($odd_even + 1) % 2;
		$points = intval ($row1['sumpoints']);
		$plac += 1;
		
		$season_toplist->setCurrentBlock ('NAME');
		$season_toplist->setVariable ('odd_even', $odd_even);
		$season_toplist->setVariable ('place', $plac);
		$season_toplist->setVariable ('list_url', $tch_listpage_url);
		$season_toplist->setVariable ('id', intval ($row1['player_id']));
		$season_toplist->setVariable ('player', tch_striptags ($row1['firstname'].' '.$row1['lastname'], TCH_SAFETAGS));
		$season_toplist->setVariable ('number_of_matches', intval ($row1['numgames']));
		$season_toplist->setVariable ('number_of_points', sprintf ("%.1f", $points/2.0));
		
		$season_toplist->parseCurrentBlock ();
	}

	$s = $season_toplist->get ();

	return $s;
}

?>
