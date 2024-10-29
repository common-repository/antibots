<?php
/**
 * @author Bill Minozzi
 * @copyright 2020
 * @ Modified time: 2021-03-08 10:06:50  and 2024/07-23
 *
 */
if (!defined("ABSPATH")) {
    exit();
}
//


//$antibots_pos = stripos($antibots_request_url, "favicon.ico");
//if($antibots_pos !== false)
//   return;


if (!isset($_COOKIE["antibots_cookie"])) {

    add_action("wp_enqueue_scripts", "antibots_include_scripts",1000);
    add_action("admin_enqueue_scripts", "antibots_include_scripts",1000);

}



global $wpdb;
require_once ABSPATH . "wp-admin/includes/upgrade.php";
//$antibots_ip = antibots_findip();
$userAgentOri = antibots_get_ua();
if (empty(trim($antibots_admin_email))) {
    $antibots_admin_email = sanitize_email(get_option("admin_email", ""));
}
$antibots_ua = strtolower(trim(strtolower($userAgentOri)));
$ip_server = trim(sanitize_text_field($_SERVER["SERVER_ADDR"]));
$antibots_string_whitelist = trim(
    sanitize_text_field(get_site_option("antibots_string_whitelist", ""))
);
$aantibots_string_whitelist = explode(" ", $antibots_string_whitelist);
$antibots_ip_whitelist = trim(
    sanitize_text_field(get_site_option("antibots_ip_whitelist", ""))
);
$aantibots_ip_whitelist = explode(" ", $antibots_ip_whitelist);
$antibots_maybe_search_engine = antibots_maybe_search_engine($antibots_ua);
if (version_compare(trim(ANTIBOTSVERSION), trim($antibots_version)) > 0) {
    // Version was changed - Update made.
    antibots_create_db_visitors();
    antibots_create_db_finger();
    antibots_create_whitelist();
    if (!add_option("antibots_version", ANTIBOTSVERSION)) {
        update_option("antibots_version", ANTIBOTSVERSION);
    }
}
/* ---------------STEP 1 Tem Fingerprint? ------------------ */
$antibots_is_human = "?";
// auto declare is s.e ?
$antibots_mysearch = [
    "bingbot",
    "googlebot",
    "msn.com",
    "slurp",
    "facebookexternalhit",
    "AOL",
    "Baidu",
    "Bingbot",
    "DuckDuck",
    "Teoma",
    "Yahoo",
    "seznam",
    "Yandex",
    "Twitterbot",
    "facebookexternalhit",
];
for ($i = 0; $i < count($antibots_mysearch); $i++) {
    if (stripos($antibots_ua, $antibots_mysearch[$i]) !== false) {
        $antibots_is_human = "0";
    }
}
add_action("wp_head", "antibots_ajaxurl");
// auto declared s. e. == 0
$qrow = 0;
$antibots_pos = stripos($antibots_request_url, "_record_fingerprint");

$antibots_fingerprint_filed = '';

if ($antibots_is_human !== "0") {
    $result = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT fingerprint, deny FROM {$wpdb->prefix}antibots_fingerprint WHERE ip = %s AND fingerprint != '' LIMIT 1",
            $antibots_ip
        )
    );
    if (!empty($wpdb->last_error)) {
        antibots_create_db_finger();
        $qrow = 0;
    } else {
        $qrow = $wpdb->num_rows;
    }
    //
    if ($qrow < 1 and !isset($_COOKIE["antibots_cookie"])) {
        if (
            !antibots_whitelist_string($antibots_ua) &&
            !antibots_whitelist_IP($antibots_ip) &&
            $antibots_pos === false &&
            antibots_check_wordpress_logged_in_cookie() &&
            !antibots_isourserver()
        ) {
            add_action("wp_enqueue_scripts", "antibots_include_scripts",1000);
            add_action("admin_enqueue_scripts", "antibots_include_scripts",1000);
        }
    }
} // 	if( $antibots_is_human !== '0')
if ($qrow < 1 && !isset($_COOKIE["antibots_cookie"])) {
    if ($antibots_is_human != "0") {
        if (antibots_first_time() > 0) {
            $antibots_is_human = "0";
        } else {
            $antibots_is_human = "?";
        }
    }
} elseif (
    !antibots_whitelist_string($antibots_ua) &&
    !antibots_whitelist_IP($antibots_ip) &&
    !$antibots_maybe_search_engine &&
    $antibots_pos === false &&
    !antibots_isourserver() &&
    antibots_check_wordpress_logged_in_cookie()
) {
    $antibots_fingerprint_filed = "";
    //$antibots_fingerprint_deny_filed = 0;
    // Tem Fingerprint
    if (isset($result[0]->fingerprint)) {
        $antibots_fingerprint_filed = trim($result[0]->fingerprint);
        //$antibots_fingerprint_deny_filed = trim( $result[0]->deny );
        // $fingerprint_deny_filed  = trim($row2020[1]);
    }
    if (
        isset($_COOKIE["antibots_cookie"]) and
        empty($antibots_fingerprint_filed)
    ) {
        $antibots_fingerprint_filed = sanitize_text_field(
            $_COOKIE["antibots_cookie"]
        );
    }
    if(!isset($_COOKIE["antibots_cookie"]) || empty($antibots_fingerprint_filed)){
        add_action("wp_enqueue_scripts", "antibots_include_scripts",1000);
        add_action("admin_enqueue_scripts", "antibots_include_scripts",1000);
    }
    if (!empty($antibots_fingerprint_filed)) {
        $antibots_is_human = "1";
    }
} else {
     if(!isset($_COOKIE["antibots_cookie"]) || empty($antibots_fingerprint_filed)){
      $antibots_is_human = "1";
      add_action("wp_enqueue_scripts", "antibots_include_scripts",1000);
      add_action("admin_enqueue_scripts", "antibots_include_scripts",1000);
    }
}
//
// -----------------End step 1----------------------
add_action(
    "wp_ajax_antibots_record_fingerprint",
    "antibots_record_fingerprint"
);
add_action(
    "wp_ajax_nopriv_antibots_record_fingerprint",
    "antibots_record_fingerprint"
);
add_action("wp_ajax_antibots_add_whitelist", "antibots_add_whitelist");
add_action("wp_ajax_nopriv_antibots_add_whitelist", "antibots_add_whitelist");
$antibots_now = strtotime("now");
$antibots_after = strtotime("now") + 3600;
add_filter("custom_menu_order", "antibots_change_note_submenu_order");
add_action("wp_ajax_antibots_get_ajax_data", "antibots_get_ajax_data");
add_action("wp_ajax_nopriv_antibots_get_ajax_data", "antibots_get_ajax_data");
if (antibots_check_wordpress_logged_in_cookie()) {
    if (isset($_GET["page"])) {
        $page = sanitize_text_field($_GET["page"]);
        if (
            $page == "anti_bots_plugin" or
            $page == "antibots_my-custom-submenu-page" or
            $page == "settings-anti-bots"
        ) {
            // add_filter('contextual_help', 'antibots_contextual_help', 10, 3);
            add_action("current_screen", "antibots_contextual_help");
            function antibots_contextual_help()
            {
                require_once ABSPATH . "wp-admin/includes/screen.php";
                $screen = get_current_screen();
                $myhelp =
                    "<br />" .
                    __(
                        "Read the StartUp guide at Anti Bots Settings page. (WP Dashboard => Anti Bots = Settings)",
                        "antibots"
                    );
                $myhelp .= "<br />";
                $myhelp .=
                    "<br />" .
                    __(
                        "Go to Dashboard Page for more information, Online Guide and Support. (WP Dashboard => Anti Bots = Dashboard)",
                        "antibots"
                    );
                $myhelp .= "<br />";
                $myhelp .=
                    "<br />" .
                    __(
                        "Go to Visitors Log Page for details about the visits. (WP Dashboard => Anti Bots = Visitors Log)",
                        "antibots"
                    );
                $myhelp .= "<br />";
                $myhelp .= "<br />";
                $myhelp .= __("Visit the", "antibots");
                $myhelp .=
                    '&nbsp<a href="http://antibotsplugin.com" target="_blank">';
                $myhelp .= __("plugin site", "antibots");
                $myhelp .= "</a>&nbsp;";
                $myhelp .= __(
                    "for more details, Support and online guide.",
                    "antibots"
                );
                $myhelptable = "<br />";
                $myhelptable .= "Main Response Codes:";
                $myhelptable .= "<br />";
                $myhelptable .= "200 = Normal (content is empty if is a bot)";
                $myhelptable .= "<br />";
                $myhelptable .= '403 = Forbidden (page content doesn\'t show)';
                $myhelptable .= "<br />";
                $myhelptable .= "404 = Page Not Found";
                $myhelptable .= "<br />";
                $myhelptable .= "<br />";
                $myhelptable .= "Access:";
                $myhelptable .= "<br />";
                $myhelptable .=
                    "Masked: The actual page content is not being displayed.";
                $myhelptable .= "<br />";
                $myhelptable .= "Denied: With response code 403.";
                $myhelptable .= "<br />";
                $myhelptable .= "OK = The page content is being displayed";
                $myhelptable .= "<br />";
                $myhelptable .= "<br />";
                $myhelptable .= "Main Methods:";
                $myhelptable .= "<br />";
                $myhelptable .=
                    "GET is used to request data from a specified resource.";
                $myhelptable .= "<br />";
                $myhelptable .=
                    "POST is used to send data to a server to create/update a resource.";
                $myhelptable .= "<br />";
                $myhelptable .=
                    "HEAD is almost identical to GET, but without the response body.";
                $myhelptable .= "<br />";
                $myhelptable .= "<br />";
                $myhelptable .= "URL BLANK:";
                $myhelptable .= "<br />";
                $myhelptable .= "It is your Homepage.";
                $myhelptable .= "<br />";
                $myhelptable .= "<br />";
                $screen->add_help_tab([
                    "id" => "antibots-overview-tab",
                    "title" => __("Overview", "antibots"),
                    "content" => "<p>" . $myhelp . "</p>",
                ]);
                $screen->add_help_tab([
                    "id" => "antibots-visitors-log",
                    "title" => __("Visitors Log", "antibots"),
                    "content" => "<p>" . $myhelptable . "</p>",
                ]);
                return;
            }
        }
    }
}
/////////////////////     FUNCTIONS //

function antibots_page_template($template)
{
    return ANTIBOTSPATH . "assets/php/content_antibots.php";
}
function antibots_add_menu_items()
{
    $antibots_table_page = add_submenu_page(
        "anti_bots_plugin", // $parent_slug
        "Visitors Table", // string $page_title
        "Visitors Table", // string $menu_title
        "manage_options", // string $capability
        "antibots_my-custom-submenu-page",
        "antibots_render_list_page"
    );
}
function antibots_change_note_submenu_order($menu_ord)
{
    global $submenu;
    function antibots_str_replace_json($search, $replace, $subject)
    {
        return json_decode(
            str_replace($search, $replace, json_encode($subject)),
            true
        );
    }
    $key = "Anti Bots";
    $val = "Dashboard";
    $submenu = antibots_str_replace_json($key, $val, $submenu);
}
function antibots_alertme($userAgentOri)
{
    global $antibotsserver, $antibots_admin_email, $antibots_ip;
    $subject = __("Detected Bot on", "antibots") . " " . $antibotsserver;
    $message[] = __("Bot was detected and blocked.", "antibots");
    $message[] = "";
    $message[] =
        __("Date", "antibots") . "..............: " . date("F j, Y, g:i a");
    $message[] = __("User Agent", "antibots") . "........: " . $userAgentOri;
    $message[] = __("Robot IP Address", "antibots") . "..: " . $antibots_ip;
    $message[] = "";
    $message[] = __("eMail sent by Anti Bots Plugin.", "antibots");
    $message[] = __(
        "You can antibots emails at the Notifications Settings Tab.",
        "antibots"
    );
    $message[] = __("Dashboard => Anti Bots => Settings.", "antibots");
    $message[] = "";
    $msg = join("\n", $message);
    if (empty(trim($antibots_admin_email))) {
        $antibots_admin_email = sanitize_text_field(
            get_option("admin_email", "")
        );
    }
    $x = wp_mail($antibots_admin_email, $subject, $msg);
    return;
}

function antibots_plugin_was_activated()
{
    global $wp_antibots_blacklist;
    add_option("antibots_was_activated", "1");
    update_option("antibots_was_activated", "1");
    $antibots_installed = trim(get_option("antibots_installed", ""));
    if (empty($antibots_installed)) {
        add_option("antibots_installed", time());
        update_option("antibots_installed", time());
    }
    antibots_create_db_visitors();
    antibots_create_db_finger();
    antibots_create_whitelist();
}
function antibots_create_db_visitors()
{
    global $wpdb;
    require_once ABSPATH . "wp-admin/includes/upgrade.php";
    $table = $wpdb->prefix . "antibots_visitorslog";
    if (antibots_tablexist($table)) {
        return;
    }
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table (
        `id` mediumint(9) NOT NULL AUTO_INCREMENT,
        `access` varchar(6) NOT NULL,
        `date` timestamp NOT NULL,
        `ip` varchar(50) NOT NULL,
        `human` varchar(5) NOT NULL,
        `response` varchar(5) NOT NULL,
        `method` varchar(10) NOT NULL,
        `url` text NOT NULL,
        `referer` text NOT NULL,  
        `ua` TEXT NOT NULL,
    UNIQUE (`id`)
    ) $charset_collate;";
    dbDelta($sql);
    $query = "CREATE INDEX ip ON " . $table . "(ip(50))";
    $wpdb->get_results($query);
    $query = "CREATE INDEX ua ON " . $table . "(ua(100))";
    $wpdb->get_results($query);
    $query = "CREATE INDEX referer ON " . $table . "(referer(100))";
    $wpdb->get_results($query);
    $query = "CREATE INDEX url ON " . $table . "(url(100))";
    $wpdb->get_results($query);
    $query = "CREATE INDEX method ON " . $table . "(method(10))";
    $wpdb->get_results($query);
    $query = "CREATE INDEX response ON " . $table . "(response(5))";
    $wpdb->get_results($query);
    $query = "CREATE INDEX date ON " . $table . "(date)";
    $wpdb->get_results($query);
}
function antibots_create_db_finger()
{
    global $wpdb;
    require_once ABSPATH . "wp-admin/includes/upgrade.php";
    $table = $wpdb->prefix . "antibots_fingerprint";
    if (antibots_tablexist($table)) {
        return;
    }
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table (
        `id` mediumint(9) NOT NULL AUTO_INCREMENT,
        `data` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `ip` varchar(50) NOT NULL,
        `fingerprint` text NOT NULL,
        `deny` tinyint(4) NOT NULL,
    UNIQUE (`id`),
    UNIQUE (`ip`)
    ) $charset_collate;";
    dbDelta($sql);
}
function antibots_plugin_act_message()
{
    echo '<div class="updated"><p>';
    $antibots_msg =
        '<img width="200" src="' .
        ANTIBOTSURL .
        '/assets/images/infox350.png" />';
    $antibots_msg .= "<h2>";
    $antibots_msg .= __("Anti Bots Plugin was activated!", "antibots");
    $antibots_msg .= "</h2>";
    $antibots_msg .= "<h3>";
    $antibots_msg .= __(
        "For details and help, take a look at Anti Bots at your left menu",
        "antibots"
    );
    $antibots_msg .= "<br />";
    $antibots_msg .=
        '  <a class="button button-primary" href="admin.php?page=anti_bots_plugin">';
    $antibots_msg .= __("or click here", "antibots");
    $antibots_msg .= "</a>";
    echo $antibots_msg;
    echo "</p></h3></div>";
}
if (antibots_check_wordpress_logged_in_cookie()) {
    if (get_option("antibots_was_activated", "0") == "1") {
        add_action("admin_notices", "antibots_plugin_act_message");
        $r = update_option("antibots_was_activated", "0");
        if (!$r) {
            add_option("antibots_was_activated", "0");
        }
    }
}
function antibots_get_ua()
{
    if (!isset($_SERVER["HTTP_USER_AGENT"])) {
        return "Blank";
    }
    $ua = trim(sanitize_text_field($_SERVER["HTTP_USER_AGENT"]));
    $ua = antibots_clear_extra($ua);
    return $ua;
}
function antibots_clear_extra($mystring)
{
    $mystring = str_replace('$', "S;", $mystring);
    $mystring = str_replace("{", "!", $mystring);
    $mystring = str_replace("shell", "chell", $mystring);
    $mystring = str_replace("curl", "kurl", $mystring);
    $mystring = str_replace("<", "&lt;", $mystring);
    return $mystring;
}
function antibots_tablexist($table)
{
    global $wpdb;
    $table_name = $table;
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
        return true;
    } else {
        return false;
    }
}
function antibots_response()
{
    global $antibots_active;
    global $userAgentOri;
    global $antibots_my_radio_report_all_visits;
    if ($antibots_my_radio_report_all_visits == "yes") {
        antibots_alertme($userAgentOri);
    }
    if ($antibots_active == "yes") {
        http_response_code(403);
        antibots_record_log("403");
        header("HTTP/1.1 403 Forbidden");
        header("Status: 403 Forbidden");
        header("Connection: Close");
        exit();
    } else {
        antibots_record_log("403");
    }
}
function antibots_check_fingersprint()
{
    global $wpdb;
    global $antibots_ip;
    require_once ABSPATH . "wp-admin/includes/upgrade.php";
    $table_name = $wpdb->prefix . "antibots_fingerprint";
    $query =
        "select ip FROM " .
        $table_name .
        " WHERE ip = '" .
        $antibots_ip .
        "'
            AND `fingerprint` != '' limit 1";
    if ($wpdb->get_var($query) > 0) {
        return true;
    } else {
        return false;
    }
}
function antibots_create_whitelist()
{
    $mywhitelist = [
        "AOL",
        "Baidu",
        "Bingbot",
        "codecanyon",
        "DuckDuck",
        "envato",
        "facebook",
        "paypal",
        "Stripe",
        "SiteUptime",
        "Teoma",
        "themeforest",
        "Yahoo",
        "slurp",
        "seznam",
        "Twitterbot",
        "webgazer",
        "Yandex",
    ];
    $text = "";
    for ($i = 0; $i < count($mywhitelist); $i++) {
        $text .= $mywhitelist[$i] . PHP_EOL;
    }
    if (!add_option("antibots_string_whitelist", $text)) {
        update_option("antibots_string_whitelist", $text);
    }
}
/*
function antibots_maybe_search_engine($ua)
{
    global $antibots_ip;
    $ua = trim(strtolower($ua));
    $mysearch = array(
        'googlebot',
        'bingbot',
        'slurp',
        'Twitterbot',
        'facebookexternalhit'
    );
    for ($i = 0; $i < count($mysearch); $i++) {
        if (stripos($ua, $mysearch[$i]) !== false) {
            if ($mysearch[$i] == 'facebookexternalhit') {
                return true;
            }
            if ($mysearch[$i] == 'Twitterbot') {
                return true;
            }
            $host = strip_tags(gethostbyaddr($antibots_ip));
            $mysearch1 = array(
                'googlebot',
                'msn.com',
                'slurp',
                'facebookexternalhit'
            );
            if (stripos($host, $mysearch1[$i]) !== false) {
                return true;
            }
        }
    }
    return false;
}
*/
function antibots_maybe_search_engine($ua)
{
    global $antibots_ip;
    // Check if the user agent matches Facebook or Twitter bots
    if (
        stripos($ua, "facebookexternalhit") !== false ||
        stripos($ua, "twitterbot") !== false
    ) {
        // If it matches Facebook or Twitter bots, return true
        return true;
    }
    // List of known search engine bots for host verification
    $search_bots_host = [
        "googlebot",
        "Google-InspectionTool",
        "msn.com",
        "slurp",
    ];
    // Check if the origin host matches any known search engine bots
    if (
        !empty($antibots_ip) &&
        filter_var(
            $antibots_ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_RES_RANGE | FILTER_FLAG_NO_PRIV_RANGE
        )
    ) {
        try {
            // Get sanitized hostname using cached DNS lookup
            $host = antibots_cached_gethostbyaddr($antibots_ip);
            // If the hostname is valid, perform the host checks
            if ($host !== false && $host !== "" && is_string($host)) {
                $host = strtolower(trim($host));
                foreach ($search_bots_host as $bot) {
                    if (stripos($host, $bot) !== false) {
                        return true;
                    }
                }
            }
        } catch (Exception $e) {
            return true;
        }
    } else {
        return false;
    }
    // If no search engine bot or Facebook/Twitter bot is found, return false
    return false;
}
function antibots_cached_gethostbyaddr($ip)
{
    // Generate transient key based on IP address
    $transient_key = "antibots_dns_cache_" . md5($ip);
    $dns_cache_ttl = 3600; // 1 hour
    // Check if transient exists and has not expired
    if (false !== ($hostname = get_transient($transient_key))) {
        return $hostname;
    } else {
        // Perform DNS lookup
        $hostname = gethostbyaddr($ip);
        // Sanitize hostname to ensure it's safe for storage
        $hostname = sanitize_text_field($hostname);
        // Store hostname in transient with TTL
        set_transient($transient_key, $hostname, $dns_cache_ttl);
        return $hostname;
    }
}
function antibots_howmany_bots_visit()
{
    global $wpdb;
    global $antibots_ip;
    require_once ABSPATH . "wp-admin/includes/upgrade.php";
    $table_name = $wpdb->prefix . "antibots_visitorslog";
    $query =
        "select count(*) FROM " .
        $table_name .
        " WHERE ip = '" .
        $antibots_ip .
        "'
                AND `human` = 'Bot'
                ORDER BY `date` DESC";
    return $wpdb->get_var($query);
}
function antibots_howmany_human_visit()
{
    global $wpdb;
    global $antibots_ip;
    require_once ABSPATH . "wp-admin/includes/upgrade.php";
    $table_name = $wpdb->prefix . "antibots_visitorslog";
    $query =
        "select count(*) FROM " .
        $table_name .
        " WHERE ip = '" .
        $antibots_ip .
        "'
                AND `human` = 'Human'
                ORDER BY `date` DESC";
    return $wpdb->get_var($query);
}
function antibots_first_time()
{
    global $wpdb;
    global $antibots_ip;
    require_once ABSPATH . "wp-admin/includes/upgrade.php";
    $table_name = $wpdb->prefix . "antibots_visitorslog";
    $query =
        "select count(*) FROM " .
        $table_name .
        " WHERE ip = '" .
        $antibots_ip .
        "'
            AND `date` >= CURDATE() - interval 7 day ORDER BY `date` DESC";
    return $wpdb->get_var($query);
}
function antibots_record_fingerprint()
{
    global $antibots_ip;
    global $wpdb;
    global $antibots_ua;
    global $antibots_maybe_search_engine;
    if (isset($_REQUEST)) {
        $fingerprint = trim(sanitize_text_field($_REQUEST["fingerprint"]));
        if (empty($fingerprint)) {
            //error_log('li 690');
            die();
        }
        require_once ABSPATH . "wp-admin/includes/upgrade.php";
        //$table_name = "wp_antibots_fingerprint";
        $table_name = $wpdb->prefix . "antibots_fingerprint";
        $query =
            "SELECT fingerprint from " .
            $table_name .
            "
        WHERE ip = '$antibots_ip' limit 1";
        $result = $wpdb->get_row($query);
        if ($result !== null) {
            //error_log('L 703');
            $filed_fingerprint = trim($result->fingerprint);
            if (empty($filed_fingerprint)) {
                $query =
                    "UPDATE " .
                    $table_name .
                    " SET fingerprint = '" .
                    $fingerprint .
                    "' 
                WHERE ip = '" .
                    $antibots_ip .
                    "' LIMIT 1";
                $r = $wpdb->get_results($query);
            }
        } else {
            //error_log('L 718');
            $query =
                "INSERT INTO " .
                $table_name .
                " (ip, fingerprint	)
                    VALUES (
                '" .
                $antibots_ip .
                "',
                '" .
                $fingerprint .
                "')";
                //error_log($query);
            $r = $wpdb->get_results($query);
        }
        $table = $wpdb->prefix . "antibots_visitorslog";
        $query =
            "UPDATE " .
            $table .
            " SET human = 'Human'
            WHERE ip = '" .
            $antibots_ip .
            "'";
        if (!$antibots_maybe_search_engine) {
            $wpdb->get_results($query);
        }
    }
    wp_die('OK');
}
function antibots_add_whitelist()
{
    if (!isset($_REQUEST["ip"])) {
        die();
    }
    if (!filter_var($_REQUEST["ip"], FILTER_VALIDATE_IP)) {
        die();
    }
    $ip = trim(filter_var($_REQUEST["ip"], FILTER_VALIDATE_IP));
    if (empty($ip)) {
        die();
    }
    if (antibots_whitelist_IP($ip)) {
        die();
    }
    $antibots_ip_whitelist = trim(
        sanitize_text_field(get_site_option("antibots_ip_whitelist", ""))
    );
    $aantibots_ip_whitelist = explode(" ", $antibots_ip_whitelist);
    asort($aantibots_ip_whitelist);
    if (empty($antibots_ip_whitelist)) {
        $text = $ip;
    } else {
        $text = $antibots_ip_whitelist . PHP_EOL . $ip;
    }
    $text = "";
    for ($i = 0; $i < count($aantibots_ip_whitelist); $i++) {
        if (!empty($text)) {
            $text .= PHP_EOL;
        }
        $text .= $aantibots_ip_whitelist[$i];
    }
    $text .= PHP_EOL . $ip;
    if (!add_option("antibots_ip_whitelist", $text)) {
        update_option("antibots_ip_whitelist", $text);
    }
    die();
}
function antibots_whitelist_IP($antibots_ip)
{
    global $aantibots_ip_whitelist;
    if (!isset($aantibots_ip_whitelist)) {
        return false;
    }
    if (gettype($aantibots_ip_whitelist) != "array") {
        return false;
    }
    if (count($aantibots_ip_whitelist) < 1) {
        return false;
    }
    if (!empty($aantibots_ip_whitelist)) {
        for ($i = 0; $i < count($aantibots_ip_whitelist); $i++) {
            $ip_address = $aantibots_ip_whitelist[$i];
            if (stripos($ip_address, $antibots_ip) !== false) {
                return true;
            }
        }
    }
    return false;
}
function antibots_whitelist_string()
{
    global $userAgentOri;
    global $aantibots_string_whitelist;
    if (!isset($aantibots_string_whitelist)) {
        return false;
    }
    if (gettype($aantibots_string_whitelist) != "array") {
        return false;
    }
    if (count($aantibots_string_whitelist) < 1) {
        return false;
    }
    for ($i = 0; $i < count($aantibots_string_whitelist); $i++) {
        $string_name = $aantibots_string_whitelist[$i];
        if (stripos($userAgentOri, $string_name) !== false) {
            return true;
        }
    }
    return false;
}
function antibots_cron_function()
{
    global $wpdb;
    global $antibots_keep_data;
    $keep_time = $antibots_keep_data * 7;
    require_once ABSPATH . "wp-admin/includes/upgrade.php";
    $table_name = $wpdb->prefix . "antibots_visitorslog";
    $sql =
        "delete from " .
        $table_name .
        " WHERE `date` <  CURDATE() - interval " .
        $keep_time .
        " day";
    $wpdb->query($sql);
    $table_name = $wpdb->prefix . "antibots_fingerprint";
    $sql =
        "delete from " .
        $table_name .
        " WHERE `data` <  CURDATE() - interval " .
        $keep_time .
        " day";
    $wpdb->query($sql);
}
add_action("init", "antibots_create_schedule");
add_action("antibots_cron_job", "antibots_cron_function");
function antibots_create_schedule()
{
    $args = [false];
    if (!wp_next_scheduled("antibots_cron_job", $args)) {
        wp_schedule_single_event(
            time() + 24 * 3600,
            "antibots_cron_job",
            $args
        );
    }
}
function antibots_ajaxurl()
{
    echo '<script type="text/javascript">
       var ajaxurl = "' .
        admin_url("admin-ajax.php") .
        '";
     </script>';
}
function antibots_record_log($antibots_response = "")
{
    global $wpdb;
    global $antibots_ip;
    global $antibots_is_human;
    global $ip_server;
    global $antibots_method;
    global $antibots_ua;
    global $antibots_referer;
    global $antibots_request_url;
    global $antibots_access;
    global $antibots_maybe_search_engine;
    global $antibots_pos;
    $table_name = $wpdb->prefix . "antibots_visitorslog";
    if ($antibots_is_human == "0") {
        $antibots_is_human = "Bot";
        if ($antibots_maybe_search_engine) {
            $antibots_access = "OK";
        } else {
            $antibots_access = "Denied";
            if ($antibots_response !== "403") {
                $antibots_response = "";
            }
        }
    } elseif ($antibots_is_human == "?") {
        $antibots_response = "";
        $antibots_access = "Masked";
    } else {
        $antibots_is_human = "Human";
        if (empty($antibots_response)) {
            $antibots_response = http_response_code();
            $antibots_access = "OK";
        }
    }
    if ($antibots_maybe_search_engine) {
        $antibots_is_human = "Bot";
        $antibots_response = http_response_code();
    }
    //$antibots_pos = stripos(ANTIBOTSURL, '_fingerprint');
    if ($ip_server == $antibots_ip or $antibots_pos !== false) {
        return;
    }
    $query =
        "INSERT INTO " .
        $table_name .
        " (ip, access, method, url, referer, ua, response, human)
        VALUES (
     '" .
        $antibots_ip .
        "',
     '" .
        $antibots_access .
        "',
     '" .
        $antibots_method .
        "',
     '" .
        $antibots_request_url .
        "',
     '" .
        $antibots_referer .
        "',
     '" .
        $antibots_ua .
        "',
     '" .
        $antibots_response .
        "',
     '" .
        $antibots_is_human .
        "')";
    $r = $wpdb->get_results($query);
    return;
}
function antibots_double_check()
{
    global $wpdb;
    global $antibots_is_human;
    require_once ABSPATH . "wp-admin/includes/upgrade.php";
    $table_name = $wpdb->prefix . "antibots_fingerprint";
    $query =
        "select * from " .
        $table_name .
        " WHERE fingerprint != ''
    ORDER by data DESC LIMIT 3 ";
    $results = $wpdb->get_results($query);
    if ($results) {
        $table_name = $wpdb->prefix . "antibots_visitorslog";
        foreach ($results as $result) {
            $ip = $result->ip;
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                $query =
                    "UPDATE " .
                    $table_name .
                    " SET human = 'Human', access = 'OK'
            WHERE ip = '" .
                    $ip .
                    "'
            AND access = 'Denied'";
                $wpdb->get_results($query);
            }
        }
    }
    if ($antibots_is_human != "1") {
        sleep(3);
    }
}
add_action("shutdown", "antibots_double_check");
function antibots_get_ajax_data()
{
    require_once "server_processing.php";
    wp_die();
}
function antibots_isourserver()
{
    global $antibots_ip;
    try {
        if (isset($_SERVER["SERVER_ADDR"])) {
            $server_ip = sanitize_text_field($_SERVER["SERVER_ADDR"]);
        } elseif (
            function_exists("gethostname") and function_exists("gethostbyname")
        ) {
            $server_ip = sanitize_text_field(gethostbyname(gethostname()));
        } else {
            return false;
        }
    } catch (Exception $e) {
        // echo 'Caught exception: ',  $e->getMessage(), "\n";
        return false;
    }
    if (!filter_var($server_ip, FILTER_VALIDATE_IP)) {
        return false;
    }
    if ($server_ip == $antibots_ip) {
        return true;
    }
    if (antibots_whitelist_ip($antibots_ip)) {
        return true;
    }
    if (antibots_whitelist_string()) {
        return true;
    }
    return false;
}
//
//
if (!function_exists('debug2')) {
    function debug2() {
        return true;
    }
}
