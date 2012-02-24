<?php

/**
 * Builds a rating list
 * @param date -> date of the requested rating list. If exact match cannot be found,
 *                use nearest previous date for which a list exists.
 * @return list as HTML table
 */
function tch_ratinglist ($atts, $content = null) {

	require_once 'HTML/Template/IT.php';

	global $wpdb, $table_prefix;
	global $tch_template_dir;
	global $tch_listpage_url;
	
	if ((is_null ($content)) || ($content == '')) {
		extract (shortcode_atts (array ('date' => '0'), $atts));
	} else {
		$date = $content;
	}

	$ratinglist = new HTML_Template_IT ($tch_template_dir);
	$ratinglist->loadTemplatefile ('ratinglist.tpl', true, true);
	
	$q  = "SELECT id, ratingdate FROM {$table_prefix}tch_ratingperiod";
	$q .= " WHERE ratingdate <= '%s'";
	$q .= " ORDER BY id DESC LIMIT 1";
	
	$row1 = $wpdb->get_row ($wpdb->prepare ($q, $date), ARRAY_A);
	$p = intval ($row1['id']);
	$date = $row1['ratingdate'];
	
	$q  = "SELECT DISTINCT id, firstname, lastname, player_info, rating";
	$q .= " FROM {$table_prefix}tch_players, {$table_prefix}tch_rating";
	$q .= " WHERE period_id = '%d'";
	$q .= " AND {$table_prefix}tch_players.id = {$table_prefix}tch_rating.player_id";
	$q .= " ORDER BY rating DESC";
	
	$result2 = $wpdb->get_results ($wpdb->prepare ($q, $p), ARRAY_A);
	
	// Found any rating?
	if (0 == $wpdb->num_rows) {
		return '';
	}

	$prev_rating = ($p > 1);
	if ($prev_rating) {
		$p = $p - 1;
		
		$q  = "SELECT DISTINCT id, rating";
		$q .= " FROM {$table_prefix}tch_players, {$table_prefix}tch_rating";
		$q .= " WHERE period_id = '%d'";
		$q .= " AND {$table_prefix}tch_players.id = {$table_prefix}tch_rating.player_id";
		$q .= " ORDER BY rating DESC";
	
		$result3 = $wpdb->get_results ($wpdb->prepare ($q, $p), ARRAY_A);
		
		foreach ((array) $result3 AS $row3) {
			$previous_rating[$row3['id']] = $row3['rating'];
		}
	}
	
	$ratinglist->setCurrentBlock ('HEADER');
	$ratinglist->setVariable ('date', tch_striptags ($date, TCH_NOTAGS));
	$ratinglist->parseCurrentBlock ();
	
	$plac = 0;
	$odd_even = 1;

	foreach ((array) $result2 AS $row2) {
		$plac += 1;
		$odd_even = ($odd_even + 1) % 2;
		
		$ratinglist->setCurrentBlock ('NAME');
		$ratinglist->setVariable ('odd_even', $odd_even);
		$ratinglist->setVariable ('place', $plac);
		$ratinglist->setVariable ('list_url', $tch_listpage_url);
		$ratinglist->setVariable ('id', intval ($row2['id']));
		$ratinglist->setVariable ('name',
								   tch_striptags ($row2['firstname'].' '.$row2['lastname'], TCH_SAFETAGS));
		$ratinglist->setVariable ('rating', intval ($row2['rating']));
		if ($prev_rating) {
			$r = $previous_rating[$row2['id']];
			if ('' != $r)
				$ratinglist->setVariable ('rating_prev', intval ($r));
			else
				$ratinglist->setVariable ('rating_prev', 'Ny');
		} else {
			$ratinglist->setVariable ('rating_prev', '&ndash;');
		}
		$ratinglist->setVariable ('comment', tch_striptags ($row2['player_info'], TCH_SAFETAGS));

		$ratinglist->parseCurrentBlock ();
	}

	$s = $ratinglist->get();
	
	return $s;
}

?>
