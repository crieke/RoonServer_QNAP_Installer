<?php
$QPKGconfig = parse_ini_file("/etc/config/qpkg.conf", true, INI_SCANNER_RAW);
$ROON_PATH = $QPKGconfig['RoonServer']['Install_Path'];
shell_exec('sudo ' . "${ROON_PATH}" .'/helper-scripts/roon-helper-actions.sh reinstall > ' . "${ROON_PATH}" . '/web/tmp/reinstall.log');
?>
