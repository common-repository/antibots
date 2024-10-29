<?php /*
Plugin Name: Antibots
Plugin URI: http://antibotsplugin.com
Description: Anti Bots, SPAM bots and spiders. No DNS or Cloud Traffic Redirection. No Slow Down Your Site!
Version: 1.24
Text Domain: antibots
Domain Path: /language
Author: Bill Minozzi
Author URI: http://antibotsplugin.com
License:     GPL2
Copyright (c) 2016 Bill Minozzi
AntiBots is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
AntiBots_optin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with AntiBots_optin. If not, see {License URI}.
Permission is hereby granted, free of charge subject to the following conditions:
The above copyright notice and this FULL permission notice shall be included in
all copies or substantial portions of the Software.
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
DEALINGS IN THE SOFTWARE.
 */
if (!defined('ABSPATH')) {
    exit;
}
$antibots_maxMemory = @ini_get('memory_limit');
$antibots_last = strtolower(substr($antibots_maxMemory, -1));
$antibots_maxMemory = (int) $antibots_maxMemory;
if ($antibots_last == 'g') {
    $antibots_maxMemory = $antibots_maxMemory * 1024 * 1024 * 1024;
} else if ($antibots_last == 'm') {
    $antibots_maxMemory = $antibots_maxMemory * 1024 * 1024;
} else if ($antibots_last == 'k') {
    $antibots_maxMemory = $antibots_maxMemory * 1024;
}
if ($antibots_maxMemory < 134217728 /* 128 MB */ && $antibots_maxMemory > 0) {
    if (strpos(ini_get('disable_functions'), 'ini_set') === false) {
        @ini_set('memory_limit', '128M');
    }
}
global $wpdb;
define('ANTIBOTSVERSION', '1.13');
define('ANTIBOTSPATH', plugin_dir_path(__file__));
define('ANTIBOTSURL', plugin_dir_url(__file__));
define('ANTIBOTSDOMAIN', get_site_url());
define('ANTIBOTSIMAGES', plugin_dir_url(__file__) . 'assets/images');
define('ANTIBOTsPAGE', trim(sanitize_text_field($GLOBALS['pagenow'])));
$antibots_ip = antibots_findip();
$antibots_method = sanitize_text_field($_SERVER["REQUEST_METHOD"]);
$antibotsserver = sanitize_text_field($_SERVER['SERVER_NAME']);
$antibots_request_url = esc_url($_SERVER['REQUEST_URI']);
$antibots_is_admin = antibots_check_wordpress_logged_in_cookie();

if ($antibots_is_admin) {
    add_action('plugins_loaded', 'antibots_localization_init');
}


$antibots_pos = stripos($antibots_request_url, "favicon.ico");
if ($antibots_pos !== false)
    return;

if (isset($_SERVER['HTTP_REFERER']))
    $antibots_referer = sanitize_text_field($_SERVER['HTTP_REFERER']);
else
    $antibots_referer = '';
$antibots_version = trim(sanitize_text_field(get_site_option('antibots_version', '')));
if (!function_exists('wp_get_current_user')) {
    require_once(ABSPATH . "wp-includes/pluggable.php");
}
$antibots_enable_whitelist = sanitize_text_field(get_option('antibots_enable_whitelist', 'yes'));
$antibots_my_radio_report_all_visits = sanitize_text_field(get_option('antibots_my_radio_report_all_visits', 'no'));
$antibots_my_radio_report_all_visits = strtolower($antibots_my_radio_report_all_visits);
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'antibots_add_action_links');
function antibots_add_action_links($links)
{
    $mylinks = array(
        '<a href="' . admin_url('admin.php?page=settings-anti-bots') . '">Settings</a>',
    );
    return array_merge($links, $mylinks);
}
/* Begin Language */
/*
if (antibots_check_wordpress_logged_in_cookie()) {
    if (isset($_GET['page'])) {
        $page = sanitize_text_field($_GET['page']);
        if ($page == 'anti_bots_plugin' or $page == 'antibots_my-custom-submenu-page') {
            $path = dirname(plugin_basename(__FILE__)) . '/language/';
            $loaded = load_plugin_textdomain('antibots', false, $path);
        }
    }
} else {
    add_action('plugins_loaded', 'antibots_localization_init');
}
function antibots_localization_init()
{
    $path = dirname(plugin_basename(__FILE__)) . '/language/';
    $loaded = load_plugin_textdomain('antibots', false, $path);
}
*/
/* End language */
$antibots_active = sanitize_text_field(get_option('antibots_is_active', ''));
$antibots_active = strtolower($antibots_active);
$antibots_keep_data = sanitize_text_field(get_option('antibots_keep_data', '4'));
$antibots_keep_data = strtolower($antibots_keep_data);
$antibots_admin_email = trim(get_option('antibots_my_email_to', ''));
if (!empty($antibots_admin_email)) {
    if (!is_email($antibots_admin_email)) {
        $antibots_admin_email = '';
        update_option('antibots_my_email_to', '');
    }
}
require_once ANTIBOTSPATH . "functions/functions.php";
require_once ABSPATH . 'wp-includes/pluggable.php';
require_once ANTIBOTSPATH . 'dashboard/main.php';
//require_once ANTIBOTSPATH . "settings/load-plugin.php";
//require_once ANTIBOTSPATH . "settings/options/plugin_options_tabbed.php";

add_action('init', 'antibots_delay_antibots_loading');

function antibots_delay_antibots_loading()
{
    require_once ANTIBOTSPATH . "settings/load-plugin.php";
    require_once ANTIBOTSPATH . "settings/options/plugin_options_tabbed.php";
}



if (antibots_check_wordpress_logged_in_cookie()) {
    function antibots_add_admscripts()
    {
        wp_enqueue_script("jquery");
        wp_enqueue_script('jquery-ui-core');
        // wp_enqueue_style('bill-datatables', ANTIBOTSURL . 'assets/css/dataTables.bootstrap4.min.css');
        wp_enqueue_style('bill-datatables-jquery', ANTIBOTSURL . 'assets/css/jquery.dataTables.min.css');
        wp_enqueue_script('flot', ANTIBOTSURL .
            'assets/js/jquery.flot.min.js', array('jquery'));
        wp_enqueue_script('flotpie', ANTIBOTSURL .
            'assets/js/jquery.flot.pie.js', array('jquery'));
        wp_enqueue_script('botstrap', ANTIBOTSURL .
            'assets/js/bootstrap.bundle.min.js', array('jquery'));
        wp_enqueue_script('easing', ANTIBOTSURL .
            'assets/js/jquery.easing.min.js', array('jquery'));
        wp_enqueue_script('datatables1', ANTIBOTSURL .
            'assets/js/jquery.dataTables.min.js', array('jquery'));
        wp_localize_script('datatables1', 'datatablesajax', array('url' => admin_url('admin-ajax.php')));
        wp_enqueue_script('botstrap4', ANTIBOTSURL .
            'assets/js/dataTables.bootstrap4.min.js', array('jquery'));
        wp_enqueue_script('datatables2', ANTIBOTSURL .
            'assets/js/dataTables.buttons.min.js', array('jquery'));
        wp_register_script('datatables_visitors', ANTIBOTSURL .
            'assets/js/antibots_table.js', array(), '1.0', true);
        wp_enqueue_script('datatables_visitors');
    }
    add_action('admin_enqueue_scripts', 'antibots_add_admscripts', 1000);
}
require_once ANTIBOTSPATH . 'table/visitors.php';
register_activation_hook(__FILE__, 'antibots_plugin_was_activated');

// -------------------------  Step 2
if (
    !antibots_whitelist_string($antibots_ua) &&
    !antibots_whitelist_IP($antibots_ip) &&
    $antibots_pos === false &&
    !$antibots_maybe_search_engine &&
    $ip_server != $antibots_ip &&
    !antibots_check_wordpress_logged_in_cookie() &&
    $antibots_is_human != '1'
) {
    if ($antibots_is_human != '1') {
        if ($antibots_is_human == '?') {
            antibots_record_log();
            add_filter('template_include', 'antibots_page_template');
        } elseif ($antibots_is_human == '0') {
            if (antibots_howmany_bots_visit($antibots_ip) > 3 and antibots_howmany_human_visit($antibots_ip) < 1) {
                antibots_response();
            } else {
                antibots_record_log();
                add_filter('template_include', 'antibots_page_template');
            }
        }
        header("Refresh: 3;");
    }
} elseif (
    !antibots_check_wordpress_logged_in_cookie() &&
    $ip_server !== $antibots_ip &&
    $antibots_pos === false
) {
    antibots_record_log();
} else {
}
/*   ------------------------------     END STEP 2 */
function antibots_check_wordpress_logged_in_cookie()
{
    // Percorre todos os cookies definidos
    foreach ($_COOKIE as $key => $value) {
        // Verifica se algum cookie começa com 'wordpress_logged_in_'
        if (strpos($key, 'wordpress_logged_in_') === 0) {
            // Cookie encontrado
            return true;
        }
    }
    // Cookie não encontrado
    return false;
}

function antibots_include_scripts()
{
    //wp_enqueue_script("jquery");
    //wp_enqueue_script('jquery-ui-core');
    // debug2();

    wp_register_script(
        "antibots-scripts",
        ANTIBOTSURL . "assets/js/antibots_fingerprint.js",
        ["jquery"],
        null,
        true
    ); //true = footer
    wp_enqueue_script("antibots-scripts");
}

function antibots_findip()
{
    $ip = "";
    $headers = [
        "HTTP_CF_CONNECTING_IP", // CloudFlare
        "HTTP_CLIENT_IP", // Bill
        "HTTP_X_REAL_IP", // Bill
        "HTTP_X_FORWARDED", // Bill
        "HTTP_FORWARDED_FOR", // Bill
        "HTTP_FORWARDED", // Bill
        "HTTP_X_CLUSTER_CLIENT_IP", //Bill
        "HTTP_X_FORWARDED_FOR", // Squid and most other forward and reverse proxies
        "REMOTE_ADDR", // Default source of remote IP
    ];
    for ($x = 0; $x < 8; $x++) {
        foreach ($headers as $header) {
            if (!isset($_SERVER[$header])) {
                continue;
            }
            $myheader = trim(sanitize_text_field($_SERVER[$header]));
            if (empty($myheader)) {
                continue;
            }
            $ip = trim(sanitize_text_field($_SERVER[$header]));
            if (empty($ip)) {
                continue;
            }
            if (
                false !==
                ($comma_index = strpos(
                    sanitize_text_field($_SERVER[$header]),
                    ","
                ))
            ) {
                $ip = substr($ip, 0, $comma_index);
            }
            // First run through. Only accept an IP not in the reserved or private range.
            if ($ip == "127.0.0.1") {
                $ip = "";
                continue;
            }
            if (0 === $x) {
                $ip = filter_var(
                    $ip,
                    FILTER_VALIDATE_IP,
                    FILTER_FLAG_NO_RES_RANGE | FILTER_FLAG_NO_PRIV_RANGE
                );
            } else {
                $ip = filter_var($ip, FILTER_VALIDATE_IP);
            }
            if (!empty($ip)) {
                break;
            }
        }
        if (!empty($ip)) {
            break;
        }
    }
    if (!empty($ip)) {
        return $ip;
    } else {
        return "unknow";
    }
}
//
//


function antibots_localization_init()
{
    $path = ANTIBOTSPATH . 'language/';
    $locale = apply_filters('plugin_locale', determine_locale(), 'antibots');

    // Full path of the specific translation file (e.g., es_AR.mo)
    $specific_translation_path = $path . "antibots-$locale.mo";
    $specific_translation_loaded = false;

    // Check if the specific translation file exists and try to load it
    if (file_exists($specific_translation_path)) {
        $specific_translation_loaded = load_textdomain('antibots', $specific_translation_path);
    }

    // List of languages that should have a fallback to a specific locale
    $fallback_locales = [
        'de' => 'de_DE',  // German
        'fr' => 'fr_FR',  // French
        'it' => 'it_IT',  // Italian
        'es' => 'es_ES',  // Spanish
        'pt' => 'pt_BR',  // Portuguese (fallback to Brazil)
        'nl' => 'nl_NL'   // Dutch (fallback to Netherlands)
    ];

    // If the specific translation was not loaded, try to fallback to the generic version
    if (!$specific_translation_loaded) {
        $language = explode('_', $locale)[0];  // Get only the language code, ignoring the country (e.g., es from es_AR)

        if (array_key_exists($language, $fallback_locales)) {
            // Full path of the generic fallback translation file (e.g., es_ES.mo)
            $fallback_translation_path = $path . "antibots-{$fallback_locales[$language]}.mo";

            // Check if the fallback generic file exists and try to load it
            if (file_exists($fallback_translation_path)) {
                load_textdomain('antibots', $fallback_translation_path);
            }
        }
    }

    // Load the plugin
    load_plugin_textdomain('antibots', false, plugin_basename(ANTIBOTSPATH) . '/language/');
}


/*
if ($antibots_is_admin) {
    add_action('plugins_loaded', 'antibots_localization_init');
}
    */


function antibots_bill_hooking_diagnose()
{
    global $antibots_is_admin;
    // if (function_exists('is_admin') && function_exists('current_user_can')) {
    if ($antibots_is_admin and current_user_can("manage_options")) {
        $declared_classes = get_declared_classes();
        foreach ($declared_classes as $class_name) {
            if (strpos($class_name, "Bill_Diagnose") !== false) {
                return;
            }
        }
        $plugin_slug = 'recaptcha-for-all';
        $plugin_text_domain = $plugin_slug;
        $notification_url = "https://wpmemory.com/fix-low-memory-limit/";
        $notification_url2 =
            "https://wptoolsplugin.com/site-language-error-can-crash-your-site/";
        require_once dirname(__FILE__) . "/includes/diagnose/class_bill_diagnose.php";
    }
    // } 
}
add_action("init", "antibots_bill_hooking_diagnose", 10);

function antibots_bill_hooking_catch_errors()
{
    global $antibots_plugin_slug;

    $declared_classes = get_declared_classes();
    foreach ($declared_classes as $class_name) {
        if (strpos($class_name, "bill_catch_errors") !== false) {
            return;
        }
    }
    $antibots_plugin_slug = 'antibots';
    require_once dirname(__FILE__) . "/includes/catch-errors/class_bill_catch_errors.php";
}
add_action("init", "antibots_bill_hooking_catch_errors", 15);
