<?php
/**
 * @author William Sergio Minozzi
 * @copyright 2020
 */
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
define('ANTIBOTSHOMEURL', admin_url());
$antibots_urlsettings = ANTIBOTSHOMEURL . "/admin.php?page=antibots_settings33";
add_action('admin_menu', 'antibots_add_admin_menu');
function antibots_enqueue_scripts()
{
    wp_enqueue_style('bill-antibots-help-dashboard', ANTIBOTSURL . '/dashboard/css/help.css');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-dialog');
    wp_register_style('bill-jquery-help', ANTIBOTSURL .
        'assets/css/jquery-ui.css', array(), '20120208', 'all');
    wp_enqueue_style('bill-jquery-help');
}
add_action('admin_init', 'antibots_enqueue_scripts');
function antibots_add_admin_menu()
{
    global $menu;
    add_menu_page(
        'Anti Bots22',
        'Anti Bots',
        'manage_options',
        'anti_bots_plugin', // slug 
        'antibots_options_page',
        ANTIBOTSIMAGES . '/protect.png',
        '100'
    );
    include_once(ABSPATH . 'wp-includes/pluggable.php');
    $link_our_new_CPT = urlencode('edit.php?post_type=antibotsfields');
}
function antibots_settings_init()
{
    register_setting('antibots', 'antibots_settings');
}
function antibots_options_page()
{
    $current_user = wp_get_current_user();
    $username =  trim($current_user->user_firstname);
    $user = $current_user->user_login;
    $user_display = trim($current_user->display_name);
    if (empty($username))
        $username = $user;
    if (empty($username))
        $username = $user_display;
    $theme = wp_get_theme();
?>
    <!-- Begin Page -->
    <div id="antibots-theme-help-wrapper">
        <div id="antibots-not-activated"></div>
        <div id="antibots-logo">
            <img alt="logo" src="<?php echo ANTIBOTSIMAGES; ?>/logo.png" width="250px" />
        </div>
        <div id="antibots_help_title">
            <?php _e('Help and Support Page','antibots'); ?>
        </div>
    <?php
    require_once(ANTIBOTSPATH . 'dashboard/dashboard.php');
    echo '</div> <!-- "antibots-theme_help-wrapper"> -->';
} // end Function antibots_options_page
require_once(ABSPATH . 'wp-admin/includes/screen.php');
include_once(ABSPATH . 'wp-includes/pluggable.php');  ?>