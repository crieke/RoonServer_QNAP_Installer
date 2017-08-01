<?php
$SMBconfig = parse_ini_file("/etc/config/smb.conf", true, INI_SCANNER_RAW);

$directory_Roonlog = $SMBconfig['RoonServer']['path'];
$filename_Roonlog = "$directory_Roonlog" . "/RoonServer/Logs/RoonServer_log.txt";

$output_Roonlog = shell_exec('exec tail -n50 ' . $filename_Roonlog);  //only print last 50 lines

echo str_replace(PHP_EOL, '<br />', $output_Roonlog);         //add newlines
?>
