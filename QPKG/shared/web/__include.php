<?php
if (isset($_COOKIE['NAS_USER']) && isset($_COOKIE['NAS_SID'])) {
    $context = stream_context_create(array('ssl'=>array(
    'verify_peer' => false, 
    'verify_peer_name' => false
)));
    libxml_set_streams_context($context);
    $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://127.0.0.1:$_SERVER[SERVER_PORT]/cgi-bin/authLogin.cgi?sid=".$_COOKIE['NAS_SID'];
    $xml = simplexml_load_file($url);
    unset($context);
    if ( (false === $xml) || !isset($xml->authPassed) || !isset($xml->username) || !isset($xml->isAdmin) ) {
        die('Unable to retrieve xml authentication info from your qnap device.');
    }
    if ( !(bool)(int)$xml->authPassed[0] || !(bool)(int)$xml->isAdmin[0] || (string)$xml->username[0] !== $_COOKIE['NAS_USER']) {
        die('No authentic session id of an admin user!');
    }
} else {
    die('Not logged in!');
}

// Get Session ID
$strSessionID = $_COOKIE['NAS_SID'];

// Get Language
if (isset($_COOKIE['nas_lang'])) {
    $nas_lang = strtolower($_COOKIE['nas_lang']);
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
$NASLOCALHOST = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://127.0.0.1:$_SERVER[SERVER_PORT]";

$NASHOST = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[SERVER_NAME]:$_SERVER[SERVER_PORT]";

// Set specific variables by qpkg.conf
$qpkg_conf = parse_ini_file('/etc/config/qpkg.conf', 1, INI_SCANNER_RAW);
$roon_qpkg_conf = $qpkg_conf['RoonServer'];

// Make vars accessible
define("NASHOST", $NASHOST);
define("NASLOCALHOST", $NASLOCALHOST);
define("APPINSTALLPATH", $qpkg_conf['RoonServer']['Install_Path']);
define("CS_INSTALLPATH", $qpkg_conf['container-station']['Install_Path']);

$RoonVersion = file(rtrim(APPINSTALLPATH) . "/RoonServer/VERSION");

# Getting free space of database directory
if (array_key_exists('DB_Path', $roon_qpkg_conf)) {
    $qpkg_conf_db = $qpkg_conf['RoonServer']['DB_Path'];
    $originalpath = explode('/', $qpkg_conf_db);
    unset($originalpath[1]);
    $dblocation = implode('/', $originalpath);
}

if (isset($qpkg_conf_db)) {
    $db_vol_cap = disk_total_space($qpkg_conf_db);
    $db_free_space = disk_free_space($qpkg_conf_db);
    $db_perc = round(100 - (($db_free_space / $db_vol_cap) * 100));
}

if (array_key_exists('options', $roon_qpkg_conf)) {
    $qpkg_conf_options = $roon_qpkg_conf['options'];
}

?>

