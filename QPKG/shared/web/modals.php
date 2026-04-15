<?php

include_once("/home/httpd/cgi-bin/qpkg/RoonServer/__include.php");
include_once("/home/httpd/cgi-bin/qpkg/RoonServer/__functions.php");

$section = filter_var($_GET['s'], FILTER_SANITIZE_STRING);
$req = filter_var($_GET['r'], FILTER_SANITIZE_STRING);

if ($section == "log") {
    echo nl2br(
        '<div class="modal-header">' .
            '<h4 id="modal-title" class="modal-title">' . localize("MODAL_LOGFILES_HEADLINE") . '</h4>' .
            '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>' .
        '</div>' .
        '<div id="modal-body" class="modal-body modal-content">' .
            localize("MODAL_LOGFILES_DESCRIPTION1") . '<br><br>' .
            localize("MODAL_LOGFILES_DESCRIPTION2") .
            '<span id="download-area">' .
                '<a class="downloadlogs" href="#" onclick="downloadLogs()">' .
                    '<div class="fa-4x text-center" style="text-align: center;">' .
                        '<span class="fa-layers fa-fw">' .
                            '<i class="fas fa-circle"></i>' .
                            '<i class="fa-inverse fas fa-ambulance faa-passing animated" data-fa-transform="shrink-6"></i>' .
                        '</span>' .
                    '</div>' .
                    '<div class="text-center">' .
                        localize("MODAL_LOGFILES_DOWNLOAD_BTN_TEXT") .
                    '</div>' .
                '</a>' .
            '</span>' .
        '</div>' .
        '<div class="modal-footer">' .
        '<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">' . localize("BTN_CLOSE") . '</button>');
}

if ($section == "setStorage") {
    $currentPath = (!empty($dblocation) ? json_encode($dblocation) : '""');

    echo '<script>';
    echo 'var currentPath = ' . $currentPath;
    echo '</script>';

    include("content/setup.php");
}

if ($section == "about") {
    include("content/about.php");
}
?>
