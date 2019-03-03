<?php
if (!isset($_COOKIE['as_sid']) && ($_COOKIE['as_logout'] == "1") || empty($_COOKIE['as_sid'])) {
    die("not logged in! ;)");
}
$NAS_BRAND="Asustor";
// Get Session ID
$strSessionID = $_COOKIE['as_sid'];

$nas_conf_file='/etc/nas.conf';
$nas_conf = parse_ini_file($nas_conf_file, 1, INI_SCANNER_RAW);
$nas_conf_port_http = $nas_conf['Basic']['HttpPort'];


// Get Language
if (isset($_COOKIE['as_lang'])) {
    $nas_lang = strtolower(substr(($_COOKIE['as_lang']), 0, 2));
        switch ($nas_lang) {
        case "de": $nas_lang = "ger"; break;
        case "en": $nas_lang = "eng"; break;
        case "it": $nas_lang = "ita"; break;
        case "nl": $nas_lang = "dut"; break;
        case "es": $nas_lang = "spa"; break;
        case "fr": $nas_lang = "fre"; break;
        case "sv": $nas_lang = "swe"; break;
        default: $nas_lang = "eng";
        }
} else {
    $nas_locale_set =  strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));

    switch ($nas_locale_set) {
        case "de": $nas_lang = "ger"; break;
        case "en": $nas_lang = "eng"; break;
        case "it": $nas_lang = "ita"; break;
        case "nl": $nas_lang = "dut"; break;
        case "es": $nas_lang = "spa"; break;
        case "fr": $nas_lang = "fre"; break;
        case "sv": $nas_lang = "swe"; break;
        default: $nas_lang = "eng";
        }
    unset($nas_locale_set);
}
define("NAS_LANG", $nas_lang);

// Set URL
$NASLOCALHOST = "http://127.0.0.1:" . $nas_conf_port_http;
//$NASLOCALHOST = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://127.0.0.1:8000";
$NASHOST = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[SERVER_NAME]:$_SERVER[SERVER_PORT]";

// Set specific variables by RoonServer.conf
$app_conf = $string = file_get_contents('/usr/local/AppCentral/RoonServer/CONTROL/config.json');;
$roon_conf_file = '/usr/local/AppCentral/RoonServer/etc/RoonServer.conf';
$webui_status = '/usr/local/AppCentral/RoonServer/web-status';

if (file_exists($roon_conf_file)) {
    $roon_conf = parse_ini_file($roon_conf_file, 1, INI_SCANNER_RAW);

    # Getting free space of database directory
    if (array_key_exists('DB_Path', $roon_conf)) {
    $roon_conf_db = $roon_conf['DB_Path'];
    $originalpath = explode('/', $roon_conf_db);
    unset($originalpath[1]);
    $dblocation = implode('/', $originalpath);
    $db_vol_cap = disk_total_space($roon_conf['DB_Path']);
    $db_free_space = disk_free_space($roon_conf['DB_Path']);
    $db_perc = round(100 - (($db_free_space / $db_vol_cap) * 100));
    }
}

// Make vars accessible
define("NASHOST", $NASHOST);
define("NASLOCALHOST", $NASLOCALHOST);
define("APPINSTALLPATH", '/usr/local/AppCentral/RoonServer');

if (file_exists($roon_conf_file)) {
}

if (file_exists(APPINSTALLPATH . "/RoonServer/VERSION")) {
    $RoonVersion = file(rtrim(APPINSTALLPATH) . "/RoonServer/VERSION");
}

$alsafull = file_get_contents('/proc/asound/cards');
$alsaraw = fopen("/proc/asound/cards", 'r');
$alsatext = fread($alsaraw, 25000);

preg_match_all("/\[[^\]]*\]/", $alsafull, $alsa);

?>

