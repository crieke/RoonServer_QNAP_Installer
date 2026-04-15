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


define('DOCROOT', '/home/httpd/cgi-bin/qpkg/RoonServer/');
include(DOCROOT . "__include.php");
include(DOCROOT . "__functions.php");

$strVarAction = filter_var($_GET['a'], FILTER_SANITIZE_STRING);
$strVarTree = filter_var($_GET['t'], FILTER_SANITIZE_STRING);
$strModalContent = filter_var($_GET['c'], FILTER_SANITIZE_STRING);
$strOptionsContent = filter_var($_GET['o'], FILTER_SANITIZE_STRING);

if ($strVarAction == 'test') {
    echo "hi!";
}

if ($strVarAction == 'gettree') {
    $arr = getTreeAt(urlencode($strVarTree), $strSessionID);
    print json_encode($arr);
    flush();
    exit();
}

if ($strVarAction == 'dbPathIsSet') {
    $qpkg_conf = parse_ini_file('/etc/config/qpkg.conf', 1, INI_SCANNER_RAW);
    $roon_qpkg_conf = $qpkg_conf['RoonServer'];
    header('Content-Type: application/json');
    if (array_key_exists('DB_Path', $roon_qpkg_conf)) {
        echo json_encode(array(
            'success' => true
        ));
    } else {
        echo json_encode(array(
            'success' => false
        ));
    }
    return true;
}

if ($strVarAction == 'setOptions') {
    $features = explode(';', $strOptionsContent);
    shell_exec('setcfg RoonServer options "' . implode(' ', $features) . '" -f /etc/config/qpkg.conf');
    $qpkg_conf = parse_ini_file('/etc/config/qpkg.conf', 1, INI_SCANNER_RAW);
    $roon_qpkg_options = $qpkg_conf['RoonServer']['options'];
    header('Content-Type: application/json');
    //$obj = json_encode(array(
     //       'success' => true
    //));
    echo json_encode(array(
        'success' => true,
        'options' => implode(' ', $features)
    ));
    return true;
}

if ($strVarAction == 'updateformfield') {
    set_db_path(escapeshellarg('/share' . $strVarTree));
    flush();
    exit();
}

if ($strVarAction == 'downloadlogs') {
    $output = downloadLogs($strSessionID, $dblocation);
    return $output;
}

if ($strVarAction == 'restartRoonServer') {
    $startScript = '/sbin/qpkg_service restart RoonServer';
    shell_exec($startScript);
}
