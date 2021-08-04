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
    if ( (false === $xml) || !array_key_exists('authPassed', $xml) || !array_key_exists('username', $xml) || !array_key_exists('isAdmin', $xml)) {
        die('Could not verify session id.');
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

$strNoDir = 'etc';

$strVarAction = filter_var($_GET['a'], FILTER_SANITIZE_STRING);
$strVarTree = filter_var($_GET['t'], FILTER_SANITIZE_STRING);
$strModalContent = filter_var($_GET['c'], FILTER_SANITIZE_STRING);

/*
 * funktion prüfen auf etc
 */



if ($strVarAction == 'gettree') {
    $arr = getTreeAt(urlencode($strVarTree), $strSessionID);
    print json_encode($arr);
    flush();
    exit();
}

if ($strVarAction == 'checkHelperScript') {
    $running = file_exists(APPINSTALLPATH . '/.helperscript.lock');
    header('Content-Type: application/json');
    if ($running) {
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

if ($strVarAction == 'provideffmpeg') {
    header('Content-Type: application/json');
    $ffmpegfoldername="ffmpeg_For_RoonServer";
    if (!file_exists('/share' . $dblocation . '/' . $ffmpegfoldername)) {
        mkdir('/share' . $dblocation . '/' . $ffmpegfoldername, 0777, true);
    }
    echo json_encode(array(
        'success' => true,
        'dblocation' => $dblocation
    ));
}

if ($strVarAction == 'checkFfmpeg') {
    $ffmpegfoldername="ffmpeg_For_RoonServer";
    header('Content-Type: application/json');
    $ffmpegoutput = is_file('/share' . $dblocation . '/' . $ffmpegfoldername .'/ffmpeg');
    if ($ffmpegoutput !== false ) {
        echo json_encode(array(
            'success' => true
        ));    
    } else {
        echo json_encode(array(
            'success' => false
        ));
    }
}

if ($strVarAction == 'removeffmpeg') {
    $ffmpegfile=APPINSTALLPATH . '/bin/ffmpeg';
    if (file_exists($ffmpegfile)) {
        unlink($ffmpegfile);
    }
}

if ($strVarAction == 'updateformfield') {
    set_db_path(escapeshellarg('/share' . $strVarTree));
    flush();
    exit();
}

if ($strVarAction == 'redownload') {
    $helper_script = APPINSTALLPATH . '/helper-scripts/roon-helper-actions.sh';
    $output = shell_exec($helper_script . ' reinstall');
    return $output;
}

if ($strVarAction == 'downloadlogs') {
    $output = downloadLogs($strSessionID, $dblocation);
    return $output;
}

if ($strVarAction == 'restartRoonServer') {
    $startScript = '/sbin/qpkg_service restart RoonServer';
    shell_exec($startScript);
}

