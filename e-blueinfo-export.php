<?php
/*
Plugin Name: e-BlueInfo Export
Plugin URI: https://github.com/bireme/e-blueinfo-export
Description: This plugin displays the e-BlueInfo content downloads in output format (XML and JSON).
Author: BIREME/PAHO/WHO
Version: 1.0
*/

define( 'EBLUEINFO_EXPORT_DIR', plugin_dir_path(__FILE__) );
define( 'EBLUEINFO_EXPORT_URL', plugin_dir_url(__FILE__) );
defined( 'PDF_SERVICE_URL' ) or define( 'PDF_SERVICE_URL', 'http://diamante15.bireme.br:9293/solr/pdfs/select/' );
defined( 'EBLUEINFO_SERVICE_URL' ) or define( 'EBLUEINFO_SERVICE_URL', 'https://fi-admin-api.bvsalud.org/api/community/' );

require_once(EBLUEINFO_EXPORT_DIR . 'functions.php');

if ( !function_exists( 'do_feed_report' ) ) {
    function do_feed_report() {
        load_template( EBLUEINFO_EXPORT_DIR . 'feed-report.php' );
    }
}
add_action( 'do_feed_stats', 'do_feed_stats', 10, 1 );

register_activation_hook( __FILE__, 'eblueinfo_export_activate' );
function eblueinfo_export_activate(){
    // require e-BlueInfo plugin
    if ( ! is_plugin_active( 'e-blueinfo/e-blueinfo.php' ) and current_user_can( 'activate_plugins' ) ) {
        // stop activation redirect and show error
        wp_die( __('Sorry, but this plugin requires the e-BlueInfo Plugin to be installed and active.'), __('Error'), array( 'back_link' => true ) );
    }
}

?>
