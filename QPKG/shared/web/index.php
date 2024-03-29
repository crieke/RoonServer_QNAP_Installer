<?php
include_once("/home/httpd/cgi-bin/qpkg/RoonServer/__include.php");
include_once("/home/httpd/cgi-bin/qpkg/RoonServer/__functions.php");
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


    <!-- Icons -->
    <link rel="apple-touch-icon" sizes="180x180" href="icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="icons/favicon-16x16.png">
    <link rel="manifest" href="icons/site.webmanifest">
    <link rel="mask-icon" href="icons/safari-pinned-tab.svg">
    <meta name="msapplication-TileColor" content="#2b5797">
    <meta name="theme-color" content="#ffffff">


    <!-- jquery asset -->
    <script src="assets/js/jquery-2.1.3.js"></script>

    <!-- popper.js asset -->
    <script src="assets/js/popper.min.js"></script>

    <!-- bootstrap asset -->
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <script src="assets/bootstrap/js/bootstrap.js"></script>

    <!-- gijgo asset -->
    <script src="assets/gijgo/js/gijgo.min.js"></script>
    <link href="assets/gijgo/css/gijgo.css" rel="stylesheet" type="text/css">

    <!-- fontawesome asset -->
    <script src="assets/fontawesome/js/fontawesome-all.min.js"></script>
    <link rel="stylesheet" href="assets/fontawesome/css/fa-svg-with-js.css">
    <link rel="stylesheet" href="assets/fontawesome-animated/font-awesome-animation.min.css">

    <script src="assets/snapsvg/snap.svg-min.js"></script>

    <!-- custom css -->
    <link rel="stylesheet" href="RoonServerQNAP.css">


</head>
<body>
<iframe id="frame" style="display:none"></iframe>
<!-- /.container -->
<div class="fullcontainer">


    <?php
    if (!empty($_GET['debug'])) {
        $debug = filter_var($_GET['debug'], FILTER_SANITIZE_NUMBER_FLOAT);
        if ($debug == 1) {
            echo localize("DEBUG_ARCH") . ': ' . php_uname('m') . '<br>';
            echo localize("DEBUG_DATABASE") . ': ' . $dblocation . '<br>';
            echo localize("DEBUG_SID") . ': ' . $_COOKIE['NAS_SID'] . '<br>';
            echo localize("DEBUG_QPKG_ROOT") . ': ' . APPINSTALLPATH . "<br>";
            echo localize("DEBUG_LANGUAGE") . ': ' . $_COOKIE['nas_lang'] . "<br>";
            echo "RoonServer PID: " . isRunning('getpid') . '<br>';
            echo localize("DEBUG_QPKG_DOCROOT") . ': ' . NASHOST . '<br>';
            echo "HomeFeature disabled: " . $multimediaDisabled . '<br>';
        }

    }
    ?>
    <nav id="navigation" class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
        <a class="navbar-brand" href="index.php">
            <img src="img/roonIcon.svg" alt="Roon Icon" style="height: 40px;">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault"
                aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarsExampleDefault">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php"><?php echo localize("NAV_MENU_ROONSERVER"); ?></a>
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
                        <a class="dropdown-item" href="https://community.roonlabs.com"
                           target="_blank"><?php echo localize("NAV_MENU_COMMUNITY"); ?></a>
                        <a class="dropdown-item" href="https://kb.roonlabs.com/Roon_Server_on_NAS"
                           target="_blank"><?php echo localize("NAV_MENU_ROON_ON_NAS"); ?></a>
                        <a class="dropdown-item" href="https://roonlabs.com/pricing.html" target="_blank">
                            <?php echo localize("NAV_MENU_TRY_ROON"); ?></a>
                    </div>
                </li>
                <li class="nav-item">
                    <a id="about" class="getModal nav-link float-right" href="#"><i class="fas fa-info-circle"></i> <?php echo localize('NAV_MENU_INFO'); ?></a>
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

    <div id="modalSection">
        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div  id="modal-content" class="modal-content" style="width: 600px;">
                </div>
            </div>
        </div>
    </div>
</div>

<script>

    // Hide Modal
    $(document).on('hidden.bs.modal', '.modal', function () {

        // Check and open modal again if helper script action is running.
        $.ajax({
            url: '<?php echo NASHOST;?>/cgi-bin/qpkg/RoonServer/ajax/ajax.php?a=checkHelperScript',
            dataType: 'json',
            success: function (response) {
                if (!response.success) {
                    $('#modalblock').html('');
                } else {
                    $('#modal').modal('show');
                }
            }
        });

        // Prevent Reload of info if database path is not set
        var action = 'dbPathIsSet';
        $.ajax({
            url: '<?php echo NASHOST;?>/cgi-bin/qpkg/RoonServer/ajax/ajax.php?a=' + action,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    $('#contentblock').load("content/info.php");
                }
            }
        });
    });


    // Function to download log files
    function downloadLogs () {
        var strUrl = '<?php echo NASHOST;?>/cgi-bin/qpkg/RoonServer/ajax/ajax.php?a=downloadlogs';
        var dlLink = "";
        var linkrcvd = false;
        $.ajax({
            url: strUrl,
            async: false,
            success: function (data) {
                dlLink = data;
                linkrcvd = true;
            }
        });



        // if download link received
        if ( linkrcvd ) {
            var ifrm = document.getElementById("frame");
            ifrm.src = dlLink;

            MODAL_LOGFILES_CHECK_DOWNLOAD_FOLDER = '<?php echo str_replace("'", "\'", localize("MODAL_LOGFILES_CHECK_DOWNLOAD_FOLDER"));?>';
            SuccessAni('#download-area', MODAL_LOGFILES_CHECK_DOWNLOAD_FOLDER);
        }
    }

    function SuccessAni(targetDiv, checkmarkText) {
        if (targetDiv == null) {targetDiv = 'modal-body'};
        var checkani = "<svg class=\"checkmark\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 52 52\"><circle class=\"checkmark__circle\" cx=\"26\" cy=\"26\" r=\"25\" fill=\"none\"/><path class=\"checkmark__check\" fill=\"none\" d=\"M14.1 27.2l7.1 7.2 16.7-16.8\"/></svg>";

        $(targetDiv).html(checkani + '<div class="text-center">' +  checkmarkText + '</div>');

        setTimeout(function() {
            $('#modal').modal('hide');
        }, 4000);
    }


    // Reinstall Roon Server
    function reinstall () {
        $('#download-area').html(
            '<br><div class="fa-4x text-center" style="text-align: center;"><svg id="loading" width="70" height="70"></svg></div>' +
            '<div class="text-center"><b><?php echo str_replace("'", "\'", localize("MODAL_REINSTALL_LOADING")); ?></b></div>'
        );

        var s = Snap("#loading");

        var svgSize = 70;
        var RoonCircle = s.circle(svgSize / 2, svgSize / 2, svgSize / 2);

        var maskRect = s.paper.rect(0, 0, svgSize / 2, svgSize);
        maskRect.attr({
            fill: "#fff"
        });
        RoonCircle.attr({
            mask: maskRect
        });

        linespacing = svgSize / 100 * 5;
        centerpoint = svgSize / 2;
        linew = svgSize / 100 * 5;
        line1h = svgSize / 100 * 90;
        line2h = svgSize / 100 * 40;
        line3h = svgSize / 100 * 60;
        line4h = svgSize / 100 * 22;
        var line1 = s.rect(centerpoint + (0 * (linew + linespacing)) + linespacing, centerpoint - (line1h / 2), linew, line1h);
        var line2 = s.rect(centerpoint + (1 * (linew + linespacing)) + linespacing, centerpoint - (line2h / 2), linew, line2h);
        var line3 = s.rect(centerpoint + (2 * (linew + linespacing)) + linespacing, centerpoint - (line3h / 2), linew, line3h);
        var line4 = s.rect(centerpoint + (3 * (linew + linespacing)) + linespacing, centerpoint - (line4h / 2), linew, line4h);

        function cw($c_height) {

            var $arr = new Array();
            $arr['y'] = (centerpoint-($c_height / 2) / 100 * svgSize);
            $arr['height'] = $c_height / 100 * svgSize;
            return $arr;

        }

        function roonAnimate() {


            line1.animate(
                cw(52), 200, function () {
                    this.animate(
                        cw(96), 200, function () {
                            this.animate(
                                cw(80), 240, function () {
                                    this.animate(
                                        cw(86), 200, function () {
                                            this.animate(
                                                cw(92), 40, function () {
                                                    this.animate(
                                                        cw(86), 40, function () {
                                                            this.animate(
                                                                {height: svgSize / 100 * 90, y: svgSize / 100 * 5}, 40
                                                            )
                                                        }
                                                    )
                                                }
                                            )
                                        }
                                    )
                                }
                            )
                        }
                    )
                }
            );

            line2.animate(
                cw(76), 280, function () {
                    this.animate(
                        cw(30), 200, function () {
                            this.animate(
                                cw(50), 240, function () {
                                    this.animate(
                                        cw(55), 120, function () {
                                            this.animate(
                                                cw(57), 40, function () {
                                                    this.animate(
                                                        cw(40), 40, function () {
                                                            this.animate(
                                                                {height: svgSize / 100 * 60, y: svgSize / 100 * 20}, 40
                                                            )
                                                        }
                                                    )
                                                })
                                        })
                                })
                        })
                });

            line3.animate(
                cw(63), 120, function () { // 3 frames
                    this.animate(
                        cw(30), 240, function () { // 6 frames
                            this.animate(
                                cw(70), 200, function () { //5 frames
                                    this.animate(
                                        cw(54), 240, function () { // 6 frames
                                            this.animate(
                                                cw(56), 40, function () { //1 frame
                                                    this.animate(
                                                        cw(42), 40, function () { // 1 frame
                                                            this.animate(
                                                                cw(58), 40), function () {
                                                                this.animate(
                                                                    {height: svgSize / 100 * 22, y: svgSize / 100 * 56}, 40
                                                                )
                                                            }
                                                        })
                                                })
                                        })
                                })
                        })
                });

            line4.animate(
                cw(14), 200, function () {
                    this.animate(
                        cw(26), 200, function () {
                            this.animate(
                                cw(8), 240, function () {
                                    this.animate(
                                        cw(20), 200, function () {
                                            this.animate(
                                                cw(62), 40, function () {
                                                    this.animate(
                                                        cw(24), 40, function () {
                                                            this.animate(
                                                                {height: svgSize / 100 * 40, y: svgSize / 100 * 30}, 40
                                                            )
                                                        }
                                                    )
                                                }
                                            )
                                        }
                                    )
                                }
                            )
                        }
                    )
                }
            );
        }

        roonAnimate();
        setInterval(roonAnimate, 1000);

        $.ajax({
            url: '<?php echo NASHOST;?>/cgi-bin/qpkg/RoonServer/ajax/ajax.php?a=redownload',
            success: function () {
                label_ReinstallDone = '<?php echo str_replace("'", "\'", localize("MODAL_REINSTALL_DONE")); ?>';
                SuccessAni('#download-area', label_ReinstallDone);
            }
        });
    }

    $('.reinstall').click(function () {
        $.ajax({
            type: "POST",
            url: "ajax/ajax.php?a=reinstall"
        });
    });

    // check if selection is valid and enable/disable button

    // Save Database Path
    function save_location() {
        var path = newdbpath;
        var action = 'updateformfield';
        var strUrl = '<?php echo NASHOST;?>/cgi-bin/qpkg/RoonServer/ajax/ajax.php?a=' + action + '&t=' + path;

        $.ajax({
            url: strUrl,
            dataType: 'json'
        });
    }

    function db_save_button() {
        if (!dbexist) {
            save_location();
            btn_LocationSaved = '<?php echo str_replace("'", "\'", localize("MODAL_SETUP_BTN_LOCATION_SAVED")); ?>';
            SuccessAni('#modal-body', btn_LocationSaved);
            dbexist = true;
            restartRoonServerAndRefresh();
        }
        else if (newdbpath != currentPath) {
            $('#modal-content').html('<div class="modal-header">' +
                '<h4 class="modal-title"><?php echo str_replace("'", "\'", localize("MODAL_SETUP_RESTART_HEADLINE")); ?></h4>' +
                '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' +
                '</div>' +
                '<div id="modal-body" class="modal-body">' +
                '<?php echo str_replace("'", "\'", localize("MODAL_SETUP_RESTART_TEXT")); ?>' +
                '<a id="saveDBrestartRoonServer" href="#" onclick="saveAndRestart()">' +
                '<div class="fa-4x text-center" style="text-align: center;">' +

                '<span class="fa-layers fa-fw">' +
                '<i class="fas fa-circle"></i>' +
                '<i class="fa-inverse fas fa-redo-alt faa-shake animated" data-fa-transform="shrink-8"></i>' +
                '</span>' +
                '</div>' +
                '<div class="text-center">' +
                '<?php echo str_replace("'", "\'", localize("MODAL_SETUP_RESTART_ROONSERVER")); ?>' +
                '</div>' +
                '</a>' +
                '</div>');
        }
        else {
            $('#modal-content').html('<div class="modal-header">' +
                '<h4 class="modal-title"><?php echo str_replace("'", "\'", localize("MODAL_SETUP_RESTART_SAME_PATH")); ?></h4>' +
                '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' +
                '</div>' +
                '<div id="modal-body" class="modal-body">' +
                '<?php echo str_replace("'", "\'", localize("MODAL_SETUP_RESTART_SAME_PATH_TEXT")); ?>' +
                '<a id="restartRoonServer" href="#" data-dismiss="modal">' +
                '<div class="fa-4x text-center" style="text-align: center;">' +
                '<span class="fa-layers fa-fw">' +
                '<i class="fas fa-exclamation-circle faa-shake animated"></i>' +
                '</span>' +
                '</div>' +
                '<div class="text-center">' +
                '<?php echo str_replace("'", "\'", localize("BTN_CLOSE")); ?>' +
                '</div>' +
                '</a>' +
                '</div>');
        }
    }

    function selectStorageSuccess() {

        btn_LocationSaved = '<?php echo str_replace("'", "\'", localize("MODAL_SETUP_BTN_LOCATION_SAVED")); ?>';
        SuccessAni('#modal-body', btn_LocationSaved);
    }

    function saveAndRestart() {
        save_location();
        restartRoonServer();
        btn_LocationSaved = '<?php echo str_replace("'", "\'", localize("MODAL_SETUP_BTN_LOCATION_SAVED")); ?>';
        SuccessAni('#modal-body', btn_LocationSaved);

    }

    function restartRoonServer() {
        $.ajax({
            url: '<?php echo NASHOST;?>/cgi-bin/qpkg/RoonServer/ajax/ajax.php?a=restartRoonServer'
        });
    }

    function restartRoonServerAndRefresh() {

        $.ajax({
            url: '<?php echo NASHOST;?>/cgi-bin/qpkg/RoonServer/ajax/ajax.php?a=restartRoonServer'
        });

        $('#restartRoonServerAudioPanel').html('');

        setTimeout(function() {
            $('#contentblock').load("content/info.php");
        }, 2000);

    }
</script>
</body>

</html>
