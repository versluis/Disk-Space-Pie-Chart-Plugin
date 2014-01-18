<?php
// Disk Space Pie Chart uninstall script
// deletes all database options when plugin is removed
// @since 0.7
// 
// Direct calls to this file are Forbidden when core files are not present
// Thanks to Ed from ait-pro.com for this  code 

if ( !function_exists('add_action') ){
header('Status: 403 Forbidden');
header('HTTP/1.1 403 Forbidden');
exit();
}

if ( !current_user_can('manage_options') ){
header('Status: 403 Forbidden');
header('HTTP/1.1 403 Forbidden');
exit();
}
// if uninstall is not called from WordPress then exit
if (!defined('WP_UNINSTALL_PLUGIN')) exit();

// delete all options from database
delete_option ('guru_unit');
delete_option ('guru_space');
delete_option ('guruspace_cached_space');

// UNSCHEDULE CRON EVENT
wp_clear_scheduled_hook( 'diskspacepiechart' ); 

// Thanks for using this plugin
// If you'd like to try again someday check out http://wpguru.co.uk where it lives and grows

?>