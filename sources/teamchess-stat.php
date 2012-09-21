<?php

/*
Plugin Name: Teamchess Stat
Plugin URI: ...
Description: Maintains and displays teamchess statistics
Version: 0.8.0
Author: Lennart Bonnevier

License: GNU General Public License (GPL), v3 (or newer)
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

define ('TCH_PLUGIN_NAME', 'teamchess-stat');
define ('TCH_PLUGIN_URL',  plugins_url() . '/' . TCH_PLUGIN_NAME . '/');
define ('TCH_PLUGIN_DIR',  WP_PLUGIN_DIR . '/' . TCH_PLUGIN_NAME . '/');
define ('TCH_NOTAGS',      0);
define ('TCH_SAFETAGS',    1);
define ('TCH_MAXTABLES',   20);

global $tch_template_dir;
global $tch_listpage_title;
global $tch_listpage_url;

/**
 * Filter for removing html tags. This is just
 * a wrapper for wp_filter_nohtml_kses.
 */
function tch_striptags ($s, $allowed_tags = TCH_NOTAGS) {
	if (TCH_NOTAGS == $allowed_tags) {
		return wp_filter_nohtml_kses ($s);		// Strips all HTML
	} elseif (TCH_SAFETAGS == $allowed_tags) {
		// <b> <i> <s> <u> are considered 'safe' but no attributes are allowed
		return wp_kses ($s,
						array ('b' => array (), 'i' => array (),
							   's' => array (), 'u' => array ()));
	} else {
		return '';					// Can't be too safe, can we?
	}
}

/* Shortcode functions and admin functions */
if (is_admin ()) {
	include 'teamchess-admin.php';
} else {
	include 'matchlist.php';
	include 'player.php';
	include 'rating-player.php';
	include 'ratinglist.php';
	include 'single-match.php';
	include 'suites.php';
	include 'toplist.php';
	include 'season-toplist.php';
}

/* Debug routine, connect when necessary */
function tch_debuginfo () {
	global $wpdb,  $table_prefix, $query_string;

	/*
	echo '<p>plugins_url: ' . plugins_url() . '</p>';
	echo '<p>plugins_dir: ' . plugins_dir() . '</p>';
	
	exit ();
	*/
}

/**
 * Called on plugin activation.
 * Loads options and records URL of statistics page.
 * If this is the first activation after installation, creates the database tables.
 */
function tch_activate () {
	global $wpdb, $table_prefix, $current_user;
	
	/* Enable localization */
	load_plugin_textdomain ('teamchess', '', basename(dirname(__FILE__)) . '/translations/');

	if (! get_option ('tch_installed')) {
		// 1st time around; define option(s), create stat page and data tables
		add_option ('tch_installed', 1);
		add_option ('tch_listpage_title', __('Statistics', 'teamchess'));
		add_option ('tch_teamsizes', implode (',', array(4,5,6,8,10,12,14,16)));
		add_option ('tch_default_teamsize', 8);
		
		get_currentuserinfo();			// An admin user must be logged in,
										// otherwise we wouldn't be here

		If (! get_page_by_title (get_option ('tch_listpage_title'))) {
			wp_insert_post (array (
					'post_type' => 'page',
					'post_title' => get_option ('tch_listpage_title'),
					'post_content' => __('Page for Teamchess statistics. Do not delete.', 'teamchess'),
					'post_status' => 'publish',
					'post_author' => $current_user->ID,
				)
			);
		}
		
		$create_tables = explode (';', file_get_contents (TCH_PLUGIN_DIR . 'create-tables.sql')); 
		$create_tables = str_replace ('%tableprefix%',  $table_prefix, $create_tables); 
		foreach ($create_tables as $create_one_table) {
			if ('' != $create_one_table) $r = $wpdb->query ($create_one_table);
		}
	}
}

/**
 * Register plugin stylesheet(s) with Wordpress.
 */
function tch_addstyle () {
	$tch_style_URL = TCH_PLUGIN_URL . TCH_PLUGIN_NAME . '.css';
	$tch_style_file = TCH_PLUGIN_DIR . TCH_PLUGIN_NAME . '.css';
    if (file_exists ($tch_style_file)) {
		wp_register_style ('tch_styles', $tch_style_URL);
		wp_enqueue_style ('tch_styles');
	}
	wp_register_style ('tch_adminstyles', TCH_PLUGIN_URL . 'teamchess-admin.css');
	wp_enqueue_style ('tch_adminstyles');
}

/**
 * Register Javascript helpers with Wordpress.
 */
function tch_addscript () {
	$tch_script_URL = TCH_PLUGIN_URL . 'new-match-helper.js';
	wp_register_script ('new-match-helper', $tch_script_URL);
	wp_enqueue_script ('new-match-helper');
// 		}
}

/**
 * Swaps page content to generate the type of statistics requested (via URL).
 * @param $the_content from Wordpress
 * @return generated content
 */
function tch_listpage_content_filter ($the_content) {
	global $tch_listpage_title;

	$my_title = single_post_title ('', FALSE);
	$q_string = html_entity_decode ($_SERVER['QUERY_STRING']);

	if ($my_title != $tch_listpage_title) {
		return $the_content;
	} else if (stripos ($q_string, 'listtype') === false) {
		return $the_content;
	} else {
		$q_parameters = array ();
		parse_str ($q_string, $q_parameters);
		switch ($q_parameters['listtype']) {
			case 'match':
				$id = (int) $q_parameters['id'];
				return do_shortcode ('[match]' . $id . '[/match]');
				break;
			case 'player':
				$id = (int) $q_parameters['id'];
				return do_shortcode ('[player]' . $id . '[/player]');
				break;
			case 'matchlist':
				return do_shortcode ('[matchlist]');
				break;
			case 'ratinglist':
				$date = tch_striptags ($q_parameters['date']);
				return do_shortcode ('[ratinglist]' . $date . '[/ratinglist]');
				break;
			case 'ratingplayer':
				$id = (int) $q_parameters['id'];
				return do_shortcode ('[ratingplayer]' . $id . '[/ratingplayer]');
				break;
			case 'suites':
				return do_shortcode ('[suites]');
				break;
			case 'toplist':
				$kind = tch_striptags ($q_parameters['kind']);
				return do_shortcode ('[toplist]' . $kind . '[/toplist]');
				break;
			case 'seasontoplist':
				$season = tch_striptags ($q_parameters['season']);
				$league = tch_striptags ($q_parameters['league']);
				return do_shortcode ("[seasontoplist season={$season} league='{$league}']");
				//echo "[seasontoplist season={$season} league={$league}]";
				break;
			default:
				return $the_content;
				break;
		}

		return __('** content-filter fallthrough **', 'teamchess');
	}
}

/**
 * Plugin init function; called every time the plugin runs.
 * Loads base dir for page templates and current title of statistics page.
 */
function tch_initplugin () {
	global $tch_template_dir;
	global $tch_listpage_title;
	global $tch_listpage_url;

	/* Enable localization */
	load_plugin_textdomain ('teamchess', '', basename(dirname(__FILE__)) . '/translations/');

	/* Global variables */
	$tch_template_dir = TCH_PLUGIN_DIR . 'templates/';
	if (WPLANG != '') {
		$tch_lang_template_dir = $tch_template_dir . 'languages/' . WPLANG . '/';
		if (file_exists ($tch_lang_template_dir)) {
			$tch_template_dir = $tch_lang_template_dir;
		}
	}
	$tch_listpage_title = get_option ('tch_listpage_title');
	if ($tch_listpage_title) {
		$listpage = get_page_by_title ($tch_listpage_title, ARRAY_A);
		$tch_listpage_url = home_url() . '/?p=' . $listpage['ID'];
	} else {
		$tch_listpage_url = home_url() . '/';
	}
	
}

/* Actions and hooks */
add_action    ('init',            'tch_initplugin');

if (is_admin ()) {
	add_action ('admin_menu',     'tch_admin_menu');
	add_action ('admin_init',     'tch_admin_options');
	add_action ('admin_enqueue_scripts', 'tch_addscript');
	add_action ('admin_print_styles', 'tch_addstyle');
	add_action ('wp_logout',      'tch_logout_cleanup');
}

add_action    ('wp_print_styles', 'tch_addstyle');
register_activation_hook (__FILE__,'tch_activate' );

add_filter    ('the_content',     'tch_listpage_content_filter');

/* Plugin shortcodes */
add_shortcode ('matchlist',       'tch_matchlist');
add_shortcode ('player',          'tch_player');
add_shortcode ('ratingplayer',    'tch_rating_player');
add_shortcode ('ratinglist',      'tch_ratinglist');
add_shortcode ('match',           'tch_single_match');
add_shortcode ('suites',          'tch_suites');
add_shortcode ('toplist',         'tch_toplist');
add_shortcode ('seasontoplist',   'tch_season_toplist');

// An early version of this plugin used shortcodes in Swedish.
// Optionally define these codes here, for backwards compatibility.
// TODO: remove this section before general release!
if ('sv_SE' == WPLANG) {
	add_shortcode ('matchlista',      'tch_matchlist');
	add_shortcode ('spelare',         'tch_player');
	add_shortcode ('ratingspelare',   'tch_rating_player');
	add_shortcode ('ratinglista',     'tch_ratinglist');
	// add_shortcode ('match',        'tch_single_match');   ** SAME CODE AS ABOVE **
	add_shortcode ('sviter',          'tch_suites');
	add_shortcode ('topplista',       'tch_toplist');
}

if (WP_DEBUG) {
	add_shortcode ('debuginfo',       'tch_debuginfo');
}

?>
