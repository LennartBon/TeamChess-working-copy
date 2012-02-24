<?php

if (defined ('WP_UNINSTALL_PLUGIN')) {
	delete_option ('tch_listpage_title');
	delete_option ('tch_installed');
}

?>
