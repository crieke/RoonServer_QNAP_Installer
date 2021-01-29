<style>
    .file-field.medium .file-path-wrapper {
    height: 3rem; }
    .file-field.medium .file-path-wrapper .file-path {
    height: 2.8rem; }
    
    .file-field.big-2 .file-path-wrapper {
    height: 3.7rem; }
    .file-field.big-2 .file-path-wrapper .file-path {
    height: 3.5rem; }
</style>
    
<?php

include_once("/home/httpd/cgi-bin/qpkg/RoonServer/__include.php");
include_once("/home/httpd/cgi-bin/qpkg/RoonServer/__functions.php");

$section = filter_var($_GET['s'], FILTER_SANITIZE_STRING);
$req = filter_var($_GET['r'], FILTER_SANITIZE_STRING);

$modalTitle = "Alsa";
$modalContent = "Content";

if ($section == "alsa") {
    echo nl2br(
        '<div class="modal-header">' .
        '<h4 id="modal-title" class="modal-title">/proc/asound/cards</h4>' .
        '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' .
        '</div>' .
        '<div id="modal-body" class="modal-body modal-content">' .
        '<pre>' . $alsatext . '</pre>' .
        '</div>' .
        '<div class="modal-footer">' .
        '<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">' . localize("BTN_CLOSE") . '</button>');
}

if ($section == "log") {
    echo nl2br(
        '<div class="modal-header">' .
            '<h4 id="modal-title" class="modal-title">' . localize("MODAL_LOGFILES_HEADLINE") . '</h4>' .
            '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' .
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
        '<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">' . localize("BTN_CLOSE") . '</button>');
}

if ($section == "reinstall") {
    echo nl2br(
        '<div class="modal-header">' .
            '<h4 id="modal-title" class="modal-title">' . localize("MODAL_REINSTALL_HEADLINE") . '</h4>' .
            '<button type="button" class="btn-close" data-dismiss="modal" aria-hidden="true">×</button>' .
        '</div>' .
        '<div id="modal-body" class="modal-body modal-content">' .
            localize("MODAL_REINSTALL_DESCRIPTION_1") . '<br>' .
            localize("MODAL_REINSTALL_DESCRIPTION_2") .'<br><br>' .
            localize("MODAL_REINSTALL_DB_UNTOUCHED") . '</b><br>' .
        '<span style="color: red;">' . localize("MODAL_REINSTALL_ROONSERVER_WILL_STOP") . '</span>' .
        '<span id="download-area">' .
            '<a class="redownload" onclick="reinstall()" href="#">' .
                '<div class="fa-4x text-center" style="text-align: center;">' .
                     '<span class="fa-layers fa-fw">' .
                        '<i class="fas fa-circle"></i>' .
                        '<i class="fa-inverse fas fa-box" data-fa-transform="shrink-8"></i>' .
                    '</span>' .
                '</div>' .
                '<div class="text-center">' .
                    localize("MODAL_REINSTALL_PROCEED_TEXT") .
                '</div>' .
            '</a>' .
        '</span>' .
        '</div>' .
        '<div class="modal-footer">' .
            '<button type="button" class="btn btn-outline-secondary btn-close" data-dismiss="modal">' . localize("BTN_CLOSE") . '</button>');
}
if ($section == "ffmpeg") {
    echo nl2br(
            '<div class="modal-header">' .
            '<h4 id="modal-title" class="modal-title"><img style="align: center; width: 30px; height: auto; margin-right: 20px;" src="img/file_ffmpeg.svg">' . localize("MODAL_FFMPEG_HEADLINE") . '</h4>' .
            '<button type="button" class="btn-close" data-dismiss="modal" aria-hidden="true">×</button>' .
        '</div>' .
        '<div id="modal-body" class="modal-body modal-content">' .
            localize("MODAL_FFMPEG_DESCRIPTION_1") . '<br>' .
            localize("MODAL_FFMPEG_DESCRIPTION_2") .'<br><br>' .
            localize("MODAL_FFMPEG_DESCRIPTION_3") . '</b><br>' .
            '<span id="download-area">' .
                '<a class="provideffmpeg" href="#">' .
                    '<div class="fa-4x text-center" style="text-align: center;">' .
                        '<span class="fa-layers fa-fw">' .
                            '<i class="fas fa-folder"></i>' .
                            '<i class="fa-inverse fas fa-plus-square" data-fa-transform="shrink-10 down-1"></i>' .
                        '</span>' .
                    '</div>' .
                    '<div class="text-center">' .
                        localize("MODAL_FFMPEG_CREATE_FOLDER") .
                    '</div>' .
                '</a>' .
            '</span>' .
        '</div>' .
        '<div class="modal-footer">' .
            '<button type="button" class="btn btn-outline-secondary btn-close" data-dismiss="modal">' . localize("BTN_CLOSE") . '</button>');
       
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

if(isset($_POST["submit"])) { 
    if (!file_exists('/share/RoonServer-Database/FFMPEG_IMPORT_FOR_ROON_SERVER')) {
        mkdir('/share/RoonServer-Database/FFMPEG_IMPORT_FOR_ROON_SERVER', 0777, true);
    } 
}
?>

<script type="text/javascript">
    $(document).ready(function(){
        $(".provideffmpeg").click(function(){
            var action = 'provideffmpeg';
            var strUrl = '<?php echo NASHOST;?>/cgi-bin/qpkg/RoonServer/ajax/ajax.php?a=' + action;

            var is_OSX = /(Mac|iPhone|iPod|iPad)/i.test(navigator.platform);
            if (is_OSX) { clientos = "apple" } else { clientos = "pc"} 
            $.ajax({
                type: 'POST',
                url: strUrl,
                success: function(data) {
                    ffmpeg_folder_info(clientos);
                }
            });
        });
    });
</script>
