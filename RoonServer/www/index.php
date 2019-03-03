<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once("__include.php");
include_once("__functions.php");
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
    <link rel="mask-icon" href="icons/safari-pinned-tab.svg" color="#2b5797">
    <meta name="msapplication-TileColor" content="#2b5797">
    <meta name="theme-color" content="#ffffff">


    <!-- jquery asset -->
    <script src="assets/js/jquery-2.1.3.js"></script>

    <!-- popper.js asset -->
    <script src="assets/js/popper.min.js"></script>

    <!-- bootstrap asset -->
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <script src="assets/bootstrap/js/bootstrap.js"></script>

    <!-- gijgo asset -->
    <script src="assets/gijgo/js/gijgo.min.js" type="text/javascript"></script>
    <link href="assets/gijgo/css/gijgo.css" rel="stylesheet" type="text/css"/>

    <!-- fontawesome asset -->
    <script src="assets/fontawesome/js/fontawesome-all.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="assets/fontawesome/css/fa-svg-with-js.css">
    <link rel="stylesheet" href="assets/fontawesome-animated/font-awesome-animation.min.css">

    <script src="assets/snapsvg/snap.svg-min.js"></script>

    <!-- custom css -->
    <link rel="stylesheet" href="RoonServer.css">


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
            echo localize("DEBUG_SID") . ': ' . $_COOKIE['as_sid'] . '<br>';
            echo localize("DEBUG_NAS_ROOT") . ': ' . APPINSTALLPATH . "<br>";
            echo localize("DEBUG_LANGUAGE") . ': ' . $_COOKIE['as_lang'] . "<br>";
            echo "Local WWW:" . NASHOST . "<br>";
            if (file_exists(APPINSTALLPATH . '/RoonServer.pid')) {
                $RoonServerPID = file_get_contents(APPINSTALLPATH . '/RoonServer.pid');
                echo "RoonServer PID: " . $RoonServerPID . '<br>';
            }
            echo localize("DEBUG_NAS_DOCROOT") . ': ' . NASHOST . '<br>';
        }
    }
    ?>

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
        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard=false data-backdrop='static' >
            <div class="modal-dialog">
                <div  id="modal-content" class="modal-content" style="width: 600px;">
                </div>
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
            url: '<?php echo NASHOST;?>/RoonServer/ajax/ajax.php?a=checkHelperScript',
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
            url: '<?php echo NASHOST;?>/RoonServer/ajax/ajax.php?a=' + action,
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
        $('.btn-close').prop('disabled', true);


        $('#download-area').html(
            '<br><div class="fa-4x text-center" style="text-align: center;"><svg id="loading" width="70" height="70"></svg></div>' +
            '<div class="text-center"><b><?php echo str_replace("'", "\'", localize("MODAL_REINSTALL_LOADING")); ?></b></div>'
        );
        roonIconAni("#loading");

        $.ajax({
                url: '<?php echo NASHOST;?>/RoonServer/ajax/ajax.php?a=downloadlogs',
                dataType: 'json',
                success: function (response) {
                    $logDate = response.logFile;
                }
        });
        
        var processRunning = setInterval(checkProcess, 3000);
        function checkProcess() {
            $.ajax({
                url: '<?php echo NASHOST;?>/RoonServer/ajax/ajax.php?a=checkHelperScript',
                dataType: 'json',
                success: function (response) {
                    console.log(response.success);
                    if (!response.success) {
                        clearInterval(processRunning);
                        var ifrm = document.getElementById("frame");
                        ifrm.src = "/RoonServer/tmp/RoonServer_Asustor_Logs_" + $logDate + ".zip"
                        MODAL_LOGFILES_CHECK_DOWNLOAD_FOLDER = '<?php echo str_replace("'", "\'", localize("MODAL_LOGFILES_CHECK_DOWNLOAD_FOLDER"));?>';
                        SuccessAni('#download-area', MODAL_LOGFILES_CHECK_DOWNLOAD_FOLDER);
                        $('.btn-close').prop('disabled', false);                                            
                    }
                }
            }); 
        }
    }

    function roonIconAni(targetDivBlock) {    
        var s = Snap(targetDivBlock);
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

        $('.btn-close').prop('disabled', true);


        $('#download-area').html(
            '<br><div class="fa-4x text-center" style="text-align: center;"><svg id="loading" width="70" height="70"></svg></div>' +
            '<div class="text-center"><b><?php echo str_replace("'", "\'", localize("MODAL_REINSTALL_LOADING")); ?></b></div>'
        );

        roonIconAni("#loading");

        if ( ! $onlyShow ) {
            $.ajax({
                url: '<?php echo NASHOST;?>/RoonServer/ajax/ajax.php?a=redownload',
                success: function () {

                }
            });
        }
        var processRunning = setInterval(checkProcess, 3000);
        function checkProcess() {
            $.ajax({
                url: '<?php echo NASHOST;?>/RoonServer/ajax/ajax.php?a=checkHelperScript',
                dataType: 'json',
                success: function (response) {
                    console.log(response.success);
                    if (!response.success) {
                        $('.btn-close').prop('disabled', false);
                        label_ReinstallDone = '<?php echo str_replace("'", "\'", localize("MODAL_REINSTALL_DONE")); ?>';
                        SuccessAni('#download-area', label_ReinstallDone);
                        clearInterval(processRunning);
                                            }
                }
            });

        }

    }

    // check if selection is valid and enable/disable button

    // Save Database Path
    function save_location() {
        var path = newdbpath;
        var action = 'updateformfield';
        var strUrl = '<?php echo NASHOST;?>/RoonServer/ajax/ajax.php?a=' + action + '&t=' + path;

        $.ajax({
            url: strUrl,
            dataType: 'json'
        });
    }

    function db_save_button() {
        if (!dbexist) {
            save_location();
            selectStorageSuccess();
            startRoonServer();
            dbexist = true;
        }
        else if ( newdbpath != '/share' + currentPath) {
            $('#modal-content').html('<div class="modal-header">\n' +
                '<h4 class="modal-title"><?php echo str_replace("'", "\'", localize("MODAL_SETUP_RESTART_HEADLINE")); ?></h4>\n' +
                '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>\n' +
                '</div>\n' +
                '<div id="modal-body" class="modal-body">\n' +
                '<?php echo str_replace("'", "\'", localize("MODAL_SETUP_RESTART_TEXT")); ?>' +
                '<a id="restartRoonServer" href="#" onclick="saveAndRestart()">\n' +
                '<div class="fa-4x text-center" style="text-align: center;">\n' +

                '<span class="fa-layers fa-fw">\n' +
                '<i class="fas fa-circle"></i>\n' +
                '<i class="fa-inverse fas fa-redo-alt faa-shake animated" data-fa-transform="shrink-8"></i>\n' +
                '</span>\n' +
                '</div>\n' +
                '<div class="text-center">\n' +
                '<?php echo str_replace("'", "\'", localize("MODAL_SETUP_RESTART_ROONSERVER")); ?>\n' +
                '</div>\n' +
                '</a>\n' +
                '</div>\n');
        }
        else {
            $('#modal-content').html('<div class="modal-header">\n' +
                '<h4 class="modal-title"><?php echo str_replace("'", "\'", localize("MODAL_SETUP_RESTART_SAME_PATH")); ?></h4>\n' +
                '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>\n' +
                '</div>\n' +
                '<div id="modal-body" class="modal-body">\n' +
                '<?php echo str_replace("'", "\'", localize("MODAL_SETUP_RESTART_SAME_PATH_TEXT")); ?>' +
                '<a id="restartRoonServer" href="#" data-dismiss="modal">\n' +
                '<div class="fa-4x text-center" style="text-align: center;">\n' +
                '<span class="fa-layers fa-fw">\n' +
                '<i class="fas fa-exclamation-circle faa-shake animated"></i>\n' +
                '</span>\n' +
                '</div>\n' +
                '<div class="text-center">\n' +
                '<?php echo str_replace("'", "\'", localize("BTN_CLOSE")); ?>\n' +
                '</div>\n' +
                '</a>\n' +
                '</div>\n');
        }
    }

    function selectStorageSuccess() {

        btn_LocationSaved = '<?php echo str_replace("'", "\'", localize("MODAL_SETUP_BTN_LOCATION_SAVED")); ?>';
        SuccessAni('#modal-body', btn_LocationSaved);
    }

    function startRoonServer() {
        $.ajax({
            url: '<?php echo NASHOST;?>/RoonServer/ajax/ajax.php?a=startRoonServer'
        });
    }

    function saveAndRestart() {
        save_location();
        restartRoonServer();
    }

    function restartRoonServer() {
        $.ajax({
            url: '<?php echo NASHOST;?>/RoonServer/ajax/ajax.php?a=restartRoonServer'
        });
        selectStorageSuccess();

    }

    function restartRoonServerAndRefresh() {

        $.ajax({
            url: '<?php echo NASHOST;?>/RoonServer/ajax/ajax.php?a=restartRoonServer'
        });

        $('#restartRoonServerAudioPanel').html('');

        setTimeout(function() {
            $('#contentblock').load("content/info.php");
        }, 2000);

    }

    $onlyShow=false;
    $( function() {
        $.ajax({
                url: '<?php echo NASHOST;?>/RoonServer/ajax/ajax.php?a=checkHelperScript',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        $('#modal-content').html('<div class="fa-4x text-center" style="text-align: center;"><svg id="loading" width="70" height="70"></svg></div>' +
                            '<div class="text-center"><b><?php echo str_replace("'", "\'", localize("MODAL_REINSTALL_LOADING")); ?></b></div>');
                        $('#modal').modal('show');
                        roonIconAni('#loading')
                        $onlyShow=true;
                        var processRunning = setInterval(checkProcess, 3000);
                        function checkProcess() {
                            $.ajax({
                                url: '<?php echo NASHOST;?>/RoonServer/ajax/ajax.php?a=checkHelperScript',
                                dataType: 'json',
                                success: function (response) {
                                    console.log(response.success);
                                    if (!response.success) {
                                        $('.btn-close').prop('disabled', false);
                                        label_ReinstallDone = '<?php echo str_replace("'", "\'", localize("MODAL_REINSTALL_DONE")); ?>';
                                        SuccessAni('#download-area', label_ReinstallDone);
                                        clearInterval(processRunning);
                                                            }
                                }
                            });

                        }
                    }
                }
        });
    });


</script>
</body>

</html>