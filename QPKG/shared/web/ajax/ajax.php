<?php
if (!isset($_COOKIE['NAS_USER']) || empty($_COOKIE['NAS_USER'])) {
    die("not logged in! ;)");
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
    $running = file_exists(QPKGINSTALLPATH . '/.helperscript.lock');
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

if ($strVarAction == 'updateformfield') {
    set_db_path($strVarTree);
    flush();
    exit();
}

if ($strVarAction == 'redownload') {
    $helper_script = QPKGINSTALLPATH . '/helper-scripts/roon-helper-actions.sh';
    $output = shell_exec($helper_script . ' reinstall');
    return $output;
}

if ($strVarAction == 'downloadlogs') {
    $output = downloadLogs($strSessionID, $dblocation);
    return $output;
}

if ($strVarAction == 'startRoonServer') {
    $startScript = QPKGINSTALLPATH . '/RoonServer.sh start';
    shell_exec($startScript);
}

if ($strVarAction == 'restartRoonServer') {
    $startScript = QPKGINSTALLPATH . '/RoonServer.sh restart';
    shell_exec($startScript);
}
