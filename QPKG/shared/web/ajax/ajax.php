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
    console . log($arr);
    print json_encode($arr);
    flush();
    exit();
}

if ($strVarAction == 'updateformfield') {
    set_db_path($strVarTree);
    flush();
    exit();
}

if ($strVarAction == 'redownload') {
    $helper_script = QPKGINSTALLPATH     . '/helper-scripts/roon-helper-actions.sh';
    $output = shell_exec($helper_script . ' reinstall');
    return $output;
}

if ($strVarAction == 'downloadlogs') {
    $output = downloadLogs($strSessionID, $dblocation);
    die($output);
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

if ($strVarAction == 'reinstallwithphp') {

    //setting vars
    $remote_file_url = 'http://download.roonlabs.com/builds/RoonServer_linuxx64.tar.bz2';
    $qpkg_root = '/share/CACHEDEV1_DATA/.qpkg/RoonServer';
    $tmp_dir = $qpkg_root . '/tmp';

    echo "Creating temp<br>";

    // create temp folder
    function tempdir($path, $prefix)
    {
        $tempfile = tempnam($path, $prefix);

        if (file_exists($tempfile)) {
            unlink($tempfile);
        }
        mkdir($tempfile);
        if (is_dir($tempfile)) {
            return $tempfile;
        }
    }
    $updateTmp = tempdir($tmp_dir, 'RS_update_');
    echo $updateTmp;

    $local_file = $updateTmp . '/RoonServer_linuxx64.tar.bz2';

    $copy = copy($remote_file_url, $local_file);

    if (!$copy) {
        echo "Doh! failed to copy $file...\n";
    } else {
        echo "WOOT! success to copy $file...\n";
    }

    shell_exec('/bin/tar xjf ' . $local_file . ' -C ' . $tmp_dir );
    unlink($local_file);
    unset($local_file);


}
