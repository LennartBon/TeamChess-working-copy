<?php

define ('TCH_MENU_NAME', __FILE__);

require_once 'new-match.php';
require_once 'new-player.php';

/**
 * Registers available settings for plugin.
 * All settings named here should exist in the WP options database.
 */
function tch_admin_options () {
	register_setting ('tch_settings', 'tch_listpage_title',   'tch_striptags');
	register_setting ('tch_settings', 'tch_teamsizes',        'tch_verify_integer_array');
	register_setting ('tch_settings', 'tch_default_teamsize', 'tch_verify_integer');
}

/**
 * Builds the plugin's administration menus
 */
function tch_admin_menu () {
	add_menu_page (__('Teamchess » Settings', 'teamchess'), 'Teamchess', 'publish_posts',
				   TCH_MENU_NAME, 'tch_settings_page',
				   plugins_url (TCH_PLUGIN_NAME . '/tch_adminmenu.png'));
	add_submenu_page (TCH_MENU_NAME, __('Teamchess » Add Match', 'teamchess'),
					  __('Add Match', 'teamchess'), 'publish_posts',
					  'tch_addmatch_menu', 'tch_addmatch_page');
	add_submenu_page (TCH_MENU_NAME, __('Teamchess » Add Player', 'teamchess'),
					  __('Add Player', 'teamchess'), 'publish_posts',
					  'tch_addplayer_menu', 'tch_addplayer_page');
	/*
	add_submenu_page (TCH_MENU_NAME, __('Teamchess » Rating', 'teamchess'),
					  __('Add Ratinglist', 'teamchess'), 'publish_posts',
					  'tch_addrating_menu', 'tch_addratinglist_page');
	*/
}

/**
 * Displays this plugin's main settings page within WP.
 * Requests settings from tch_admin_options() above to build page.
 */
function tch_settings_page() {

	if (current_user_can ('publish_posts')) {
	?>
		<div class="wrap">
		<h2><?php _e('Teamchess Settings', 'teamchess'); ?></h2>
		
		<form method="post" action="options.php">
			<?php settings_fields ('tch_settings'); ?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e('Title of page for listings', 'teamchess'); ?></th>
					<td><input type="text" name="tch_listpage_title" value="<?php echo get_option ('tch_listpage_title'); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Standard team sizes', 'teamchess'); ?></th>
					<td><input type="text" name="tch_teamsizes" value="<?php echo get_option ('tch_teamsizes'); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Pre-selected team size', 'teamchess'); ?></th>
					<td><input type="text" name="tch_default_teamsize" value="<?php echo get_option ('tch_default_teamsize'); ?>" /></td>
				</tr>
			</table>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		
		</form>
		</div>
	<?php }
	
}

/**
 * Displays admin page for adding a new match to the database.
 */
function tch_addmatch_page() {

	if (current_user_can ('publish_posts')) {
	?>
		<div class="wrap">
		<h2><?php _e("Teamchess &ndash; Add new match", 'teamchess'); ?></h2>
		
		<?php
			if (isset ($_REQUEST['matchdate'])) {
				$match_added = tch_newmatch_add ();
				if ($match_added > 0) {
					printf (__("<h3>Match no. %d registrered</h3>\n", 'teamchess'), $match_added);
				} else {
					_e("<h3>Match was not registrered!</h3>\n", 'teamchess');
				}
				unset ($_REQUEST['matchdate']);
			}
			echo tch_new_match ();
		?>

		</div>
	<?php }
}

/**
 * Displays admin page for adding a new player to the database.
 */
function tch_addplayer_page() {

	if (current_user_can ('publish_posts')) {
	?>
		<div class="wrap">
		<h2><?php _e("Teamchess &ndash; Add new player", 'teamchess'); ?></h2>
		
		<?php
			if (isset ($_REQUEST['firstname'])) {
				$player_added = tch_newplayer_add ();
				if ($player_added > 0) {
					printf (__("<h3>Player no. %d registrered</h3>\n", 'teamchess'), $player_added);
				} else {
					_e("<h3>Player was not registrered!</h3>\n", 'teamchess');
				}
				unset ($_REQUEST['firstname']);
			}
			echo tch_new_player ();
		?>

		</div>
	<?php }
}

/**
 * Displays admin page for adding a ratinglist to the database.
 * ** STUB** - this function is not yet implemented.
 */
function tch_addratinglist_page() {

	if (current_user_can ('publish_posts')) {
	?>
		<div class="wrap">
			<h2><?php _e("Teamchess &ndash; Add ratinglist", 'teamchess'); ?></h2>
			<h3><?php _e("Not yet implemented", 'teamchess'); ?></h3>

		</div>
	<?php }
}

/**
 * Validates that input string evaluates to an integer.
 * @param $s from admin interface
 * @return integer or false
 */
function tch_verify_integer ($s) {
	$i = intval ($s);
	if ($i > 0) {
		return $i;
	} else {
		return false;
	}
}

/**
 * Validates that input string consists of a series of integers.
 * @param $s from admin interface
 * @return integers sorted and assembled into a comma-separated string OR old option value on failure
 */
function tch_verify_integer_array ($s) {

	// Comma is preferred separator but accept space too
	$s = str_replace (' ', ',', $s);

	$given_teamsizes = explode (',', $s);
	foreach ($given_teamsizes as $teamsize) {
		if (! tch_verify_integer ($teamsize)) {
			// Bad input, reset to current value
			return get_option ('tch_teamsizes');	// $current_sizes;
		}
	}

	// Sort in ascending order and reassemble to a string
	sort ($given_teamsizes);
	return implode (',', $given_teamsizes);
}

?>
