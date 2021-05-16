<?php
if (isset($_COOKIE['NAS_USER']) && isset($_COOKIE['NAS_SID'])) {
    $context = stream_context_create(array('ssl'=>array(
        'verify_peer' => false, 
        "verify_peer_name"=>false
    )));
    libxml_set_streams_context($context);
    $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://127.0.0.1:$_SERVER[SERVER_PORT]/cgi-bin/authLogin.cgi?sid=".$_COOKIE['NAS_SID'];
    $xml = simplexml_load_file($url);
    unset($context);
    if ( (false === $xml) || !array_key_exists('authPassed', $xml) || !array_key_exists('username', $xml))
    {
        die('Could not verify session id.');
    }
    if ( !(bool)(int)$xml->authPassed[0] || (string)$xml->username[0] !== $_COOKIE['NAS_USER'])
    {
        die('No authentic session id!');
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

# Getting free space of database directory
if (array_key_exists('DB_Path', $roon_qpkg_conf)) {
    $qpkg_conf_db = $qpkg_conf['RoonServer']['DB_Path'];
    $originalpath = explode('/', $qpkg_conf_db);
    unset($originalpath[1]);
    $dblocation = implode('/', $originalpath);
}

if (array_key_exists('MULTIMEDIA_DISABLE_ON_START', $roon_qpkg_conf)) {
    $multimedia_disabled_on_start = $qpkg_conf['RoonServer']['MULTIMEDIA_DISABLE_ON_START'];
} else {
    $multimedia_disabled_on_start = "";

}


$RoonVersion = file(rtrim(APPINSTALLPATH) . "/RoonServer/VERSION");
$WHICH_FFMPEG = trim(shell_exec('PATH='.APPINSTALLPATH.'/bin:$PATH && which ffmpeg'));
if (strpos($WHICH_FFMPEG, APPINSTALLPATH) !== false) {
    $customFfmpeg = true;
}
else {
        $customFfmpeg = false;
}
$ffmpegVersion=trim(shell_exec('PATH='.APPINSTALLPATH.'/bin:$PATH && ffmpeg -version | sed -n "s/ffmpeg version \([^ ]*\).*/\1/p;"'));

$alsafull = file_get_contents('/proc/asound/cards');
$alsaraw = fopen("/proc/asound/cards", 'r');
$alsatext = fread($alsaraw, 25000);

preg_match_all("/\[[^\]]*\]/", $alsafull, $alsa);

if (isset($qpkg_conf_db)) {
    $db_vol_cap = disk_total_space($qpkg_conf_db);
    $db_free_space = disk_free_space($qpkg_conf_db);
    $db_perc = round(100 - (($db_free_space / $db_vol_cap) * 100));
}
$application_conf = parse_ini_file('/var/.application.conf', 1, INI_SCANNER_RAW);
$multimediaDisabled = $application_conf['DISABLE']['HomeFeature'];
unset($application_conf);
?>

