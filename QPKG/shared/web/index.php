<?php
include("__include.php");
include("__functions.php");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Christopher Rieke">
    <title>RoonServer</title>


    <!-- jquery asset -->
    <script src="assets/js/jquery-2.1.3.js"></script>

    <!-- popper.js asset -->
    <script src="assets/js/popper.js"></script>

    <!-- bootstrap asset -->
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <script src="assets/bootstrap/js/bootstrap.js"></script>

    <!-- gijgo asset -->
    <script src="assets/gijgo/js/gijgo.min.js" type="text/javascript"></script>
    <link href="assets/gijgo/css/gijgo.css" rel="stylesheet" type="text/css"/>

    <!-- Fontawesome asset -->
    <script src="assets/fontawesome/js/fontawesome-all.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="assets/fontawesome/css/fa-svg-with-js.css">
    <link rel="stylesheet" href="assets/fontawesome-animated/font-awesome-animation.min.css">

    <script src="assets/filedownload/jquery.fileDownload.js" type="text/javascript"></script>

    <!-- custom css -->
    <link rel="stylesheet" href="RoonServerQNAP.css">


</head>
<body>
<!-- /.container -->

<?php
$debug = filter_var($_GET['debug'], FILTER_SANITIZE_NUMBER_FLOAT);
?>
<div class="fullcontainer">


    <?php

    if ($debug == 1) {
        echo localize("DEBUG_ARCH") . ': ' . php_uname('m') . '<br>';
        echo localize("DEBUG_DATABASE") . ': ' . $dblocation . '<br>';
        echo localize("DEBUG_SID") . ': ' . $_COOKIE['NAS_SID'] . '<br>';
        echo localize("DEBUG_QPKG_ROOT") . ': ' . QPKGINSTALLPATH . "<br>";
        if (file_exists(QPKGINSTALLPATH . '/RoonServer.pid')) {
            $RoonServerPID = file_get_contents(QPKGINSTALLPATH . '/RoonServer.pid');
            echo "RoonServer PID: " . $RoonServerPID  . '<br>';
        }
        echo localize("DEBUG_QPKG_DOCROOT") . ': ' . QNAPDOCROOT;
    } ?>
    <nav id="navigation" class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
        <a class="navbar-brand" href="index.php">
            <img src="img/roonIcon.svg" style="height: 40px;"/>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault"
                aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarsExampleDefault">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link <?php if ($section != 'about') {
                        echo ' active';
                    } ?>" href="index.php"><?php echo localize("NAV_MENU_ROONSERVER"); ?></a>
                </li>
            </ul>
            ';

            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown pull-right dropdown-menu-right">
                    <a class="nav-link dropdown-toggle" href="#" id="dropdown01" data-toggle="dropdown"
                       aria-haspopup="true" aria-expanded="false"><?php echo localize("NAV_MENU_MORE"); ?></a>
                    <div class="dropdown-menu pull-right" aria-labelledby="dropdown01">
                        <a class="dropdown-item" href="https://roonlabs.com/downloads.html" target="_blank">
                            <?php echo localize("NAV_MENU_DOWNLOADS"); ?></a>
                        <a class="dropdown-item" href="https://community.roonlabs.com" target="_blank"><?php echo localize("NAV_MENU_COMMUNITY"); ?></a>
                        <a class="dropdown-item" href="https://kb.roonlabs.com/Roon_Server_on_NAS" target="_blank"><?php echo localize("NAV_MENU_ROON_ON_NAS"); ?></a>
                        <a class="dropdown-item" href="https://roonlabs.com/pricing.html" target="_blank">
                            <?php echo localize("NAV_MENU_TRY_ROON"); ?></a>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link float-right<?php if ($section == 'about') {
                        echo ' active';
                    } ?>" data-toggle="modal"
                       data-target="#modal-about" href="#"><i class="fas fa-info-circle"></i></a>
                </li>
            </ul>
        </div>
    </nav>

    <div id="contentblock">
    <?php
    $section = "info";
    if (!isset($dblocation)) {
        $section = "main";
    }

    include "content/{$section}.php"; ?>
    </div>
    <div id="modalblock">
        <?php include "content/modals.php"; ?>
    </div>
</body>

<script>
    $('.closemodal').click(function () {
        setTimeout(function () {
            location.reload();
        }, 500);
    });

    $('#downloadlogs').click(function () {
        var strUrl = '<?php echo QNAPDOCROOT;?>/qpkg/RoonServer/ajax/ajax.php?a=downloadlogs';

        $.ajax({
            url: strUrl,
            success: function(data){
                console.log(data);
                $.fileDownload(data);
            }
        });

        $('#download-area').html(
            '<svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"><circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/><path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/></svg><br>' +
            '<div class="text-center"><?php echo localize("MODAL_LOGFILES_CHECK_DOWNLOAD_FOLDER"); ?></div>'
        );
    });

    $('#redownload').click(function () {

        $('#btn-close-redownload').prop("disabled", true);
        $('#btn-close-redownload').addClass('disabled');
        $('#btn-x-redownload').prop("disabled", true);
        $('#btn-x-redownload').addClass('disabled');


        $('#redownload-area').html(
            '<div class="fa-4x text-center" style="text-align: center;"><i class="fas fa-sync fa-spin" ></i></div>' +
            '<div class="text-center"><?php echo localize("MODAL_REINSTALL_LOADING"); ?></div>'
        );


        $.ajax({
            url: '<?php echo QNAPDOCROOT;?>/qpkg/RoonServer/ajax/ajax.php?a=redownload',
            success: function()
            {
                $('#btn-close-redownload').prop("disabled", false);
                $('#btn-close-redownload').removeClass('disabled');
                $('#btn-x-redownload').prop("disabled", false);
                $('#btn-x-redownload').removeClass('disabled');

                $('#redownload-area').html(
                    '<svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"><circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/><path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/></svg><br>' +
                    '<div class="text-center"><?php echo localize("MODAL_REINSTALL_DONE"); ?></div>'
                );

            }
        });
    });
</script>

</html>
