<?php

$SMBconfig = parse_ini_file("/etc/config/smb.conf", true, INI_SCANNER_RAW);
$QPKGconfig = parse_ini_file("/etc/config/qpkg.conf", true, INI_SCANNER_RAW);

$zip_file = 'tmp/RoonServer_QNAP_Logs.zip';

$directory_Roonlog = $SMBconfig['RoonServer']['path'];
$dir = "$directory_Roonlog" . "/RoonServer/Logs";

// Get real path for our folder
$rootPath = realpath($dir);

// Initialize archive object
$zip = new ZipArchive();
$zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);

// Create recursive directory iterator
/** @var SplFileInfo[] $files */
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootPath),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $name => $file)
{
    // Skip directories (they would be added automatically)
    if (!$file->isDir())
    {
        // Get real and relative path for current file
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($rootPath) + 1);

        // Add current file to archive
        $zip->addFile($filePath, $relativePath);
    }
}

$dir = "$directory_Roonlog" . "/RAATServer/Logs";

// Get real path for our folder
$rootPath = realpath($dir);


// Create recursive directory iterator
/** @var SplFileInfo[] $files */
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootPath),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $name => $file)
{
    // Skip directories (they would be added automatically)
    if (!$file->isDir())
    {
        // Get real and relative path for current file
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($rootPath) + 1);

        // Add current file to archive
        $zip->addFile($filePath, $relativePath);
    }
}

$directory_bashlog = $QPKGconfig['RoonServer']['Install_Path'];
$qpkgLog = "$directory_bashlog" . "/RoonServer.log";

$new_filename = substr($qpkgLog,strrpos($qpkgLog,'/') + 1);

$zip->addFile($qpkgLog, 'RoonServer_QNAP.log');

// Zip archive will be created only after closing object

$zip->close();


header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename='.basename($zip_file));
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($zip_file));
readfile($zip_file);

?>
