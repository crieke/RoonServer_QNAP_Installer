<?php
$QPKGconfig = parse_ini_file("/etc/config/qpkg.conf", true, INI_SCANNER_RAW);

$directory_bashlog = $QPKGconfig['RoonServer']['Install_Path'];
$filename_bashlog = "$directory_bashlog" . "/web/tmp/reinstall.log";

$output_bashlog = shell_exec('exec tail -n50 ' . $filename_bashlog);  //only print last 50 lines

echo str_replace(PHP_EOL, '<br />', $output_bashlog);         //add newlines
?>
