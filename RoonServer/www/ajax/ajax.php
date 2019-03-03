<?php
if (!isset($_COOKIE['as_sid']) && ($_COOKIE['as_logout'] == "1") || empty($_COOKIE['as_sid'])) {
    die("not logged in! ;)");
}

define('DOCROOT');
if ( basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"]) ) {
    include_once("../__include.php");
    include_once("../__functions.php");
}
$strNoDir = 'etc';

$strVarAction = filter_var($_GET['a'], FILTER_SANITIZE_STRING);
$strVarTree = filter_var($_GET['t'], FILTER_SANITIZE_STRING);
$strModalContent = filter_var($_GET['c'], FILTER_SANITIZE_STRING);
/*
 * funktion prüfen auf etc
 */

if ($strVarAction == 'gettree') {
    $arr = getFoldersAt($strVarTree);
    print $arr;
    flush();
    exit();
}

if ($strVarAction == 'checkHelperScript') {
    if (file_exists(APPINSTALLPATH . '/.webui.lock') or file_exists(APPINSTALLPATH . '/web-status')) {
        $running = true;
        $currentActivity = file_get_contents(APPINSTALLPATH . '/.webui.lock');
    } else { 
        $running = false;
        unset($currentActivity);
    }

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
    $roon_conf = parse_ini_file('/usr/local/AppCentral/RoonServer/etc/RoonServer.conf', 1, INI_SCANNER_RAW);
    header('Content-Type: application/json');
    if (array_key_exists('DB_Path', $roon_conf)) {
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

if ($strVarAction == 'updateformfield') {
    set_db_path($strVarTree);
    flush();
    exit();
}

if ($strVarAction == 'redownload') {
    $bash_cmd = 'echo redownload > /usr/local/AppCentral/RoonServer/web-status';
    shell_exec($bash_cmd);
    return $output;
}

if ($strVarAction == 'downloadlogs') {
    $createLogDate = date('Ymd_His');
    $bash_cmd = "echo logs $createLogDate > /usr/local/AppCentral/RoonServer/web-status";
    shell_exec($bash_cmd);
    echo json_encode(array(
        'success' => true,
        'logFile' => $createLogDate
    ));
}

if ($strVarAction == 'startRoonServer') {
    $bash_cmd = 'echo start > /usr/local/AppCentral/RoonServer/web-status';
    shell_exec($bash_cmd);
}

if ($strVarAction == 'restartRoonServer') {
    $bash_cmd = 'echo restart > /usr/local/AppCentral/RoonServer/web-status';
    shell_exec($bash_cmd);
}