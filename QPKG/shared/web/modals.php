<?php

include_once("/home/httpd/cgi-bin/qpkg/RoonServer/__include.php");
include_once("/home/httpd/cgi-bin/qpkg/RoonServer/__functions.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<div class="modal fade" id="modal-storage" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" id="modal-content-storage" style="width: 600px;">
            <div class="modal-header">
                <h4 class="modal-title"><?php echo localize("MODAL_SETUP_HEADLINE"); ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div id="modal-body modal-body-storage" class="modal-body">
                <p><?php include "content/setup.php" ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo localize("BTN_CLOSE"); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-about" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" id="databasemodal" style="width: 600px;">
            <div class="modal-header">
                <h4 class="modal-title"><?php echo localize("MODAL_ABOUT_HEADLINE"); ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div id="modal-body" class="modal-body">
                <p><?php include "content/about.php" ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo localize("BTN_CLOSE"); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-alsa" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="databasemodal" style="width: 600px;">
            <div class="modal-header">
                <h4 class="modal-title">/proc/asound/cards</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div id="modal-body" class="modal-body">
                <pre><?php echo $alsatext; ?></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo localize("BTN_CLOSE"); ?>
                </button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modal-redownload" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" id="databasemodal" style="width: 600px;">
            <div class="modal-header">
                <h4 class="modal-title"><?php echo localize("MODAL_REINSTALL_HEADLINE"); ?></h4>
                <button type="button" id="btn-x-redownload" class="close" data-dismiss="modal" aria-hidden="true">×
                </button>
            </div>
            <div id="modal-body" class="modal-body">
                <p><?php echo localize("MODAL_REINSTALL_DESCRIPTION_1"); ?><br>
                    <?php echo localize("MODAL_REINSTALL_DESCRIPTION_2"); ?><br><br>
                    <?php echo localize("MODAL_REINSTALL_DB_UNTOUCHED"); ?></b><br>
                    <span style="color: red;"><?php echo localize("MODAL_REINSTALL_ROONSERVER_WILL_STOP"); ?></span>
                </p>
                <span id="redownload-area">
                    <a id="redownload" href="#">
                        <div class="fa-4x text-center" style="text-align: center;">
                             <span class="fa-layers fa-fw">
                                 <i class="fas fa-circle"></i>
                                 <i class="fa-inverse fas fa-box"
                                    data-fa-transform="shrink-8"></i>
                             </span>
                        </div>
                        <div class="text-center">
                            <?php echo localize("MODAL_REINSTALL_PROCEED_TEXT"); ?>
                        </div>
                    </a>
                    </span>
            </div>
            <div class="modal-footer">
                <button id="btn-close-redownload" type="button" class="btn btn-default"
                        data-dismiss="modal"><?php echo localize("BTN_CLOSE"); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-downloadlogs" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" id="databasemodal" style="width: 600px;">
            <div class="modal-header">
                <h4 class="modal-title"><?php echo localize("MODAL_LOGFILES_HEADLINE"); ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div id="modal-body" class="modal-body">
                <p><?php echo localize("MODAL_LOGFILES_DESCRIPTION1"); ?><br><br>
                    <?php echo localize("MODAL_LOGFILES_DESCRIPTION2"); ?>
                </p>
                <span id="download-area">
                    <a class="downloadlogs" href="#>">
                        <div class="fa-4x text-center" style="text-align: center;">
                            <span class="fa-layers fa-fw">
                                <i class="fas fa-circle"></i>
                                <i class="fa-inverse fas fa-ambulance faa-passing animated"
                                   data-fa-transform="shrink-6"></i>
                            </span>
                        </div>
                        <div class="text-center">
                            <?php echo localize("MODAL_LOGFILES_DOWNLOAD_BTN_TEXT"); ?>
                        </div>
                    </a>
                    </span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo localize("BTN_CLOSE"); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>

    $(document).on('hidden.bs.modal', '.modal', function () {
        $("#modalblock").html('');
        $('#modalblock').load("modals.php");
        $('#contentblock').load("content/info.php");
        console.log('Modal closed!');
    });

    $('.downloadlogs').click(function () {
        var strUrl = '<?php echo QNAPDOCROOT;?>/qpkg/RoonServer/ajax/ajax.php?a=downloadlogs';
        console.log('Download Logs called!')
        $.ajax({
            url: strUrl,
            success: function (data) {
                console.log("data: " + data);
                $.fileDownload(data);
                $('#download-area').html(
                    '<svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"><circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/><path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/></svg><br>' +
                    '<div class="text-center"><?php echo str_replace("'", "\'", localize("MODAL_LOGFILES_CHECK_DOWNLOAD_FOLDER")); ?></div>'
                );

            }
        });

    });

    $('#redownload').click(function () {

        /*  $('#btn-close-redownload').prop("disabled", true);
          $('#btn-close-redownload').addClass('disabled');
          $('#btn-x-redownload').prop("disabled", true);
          $('#btn-x-redownload').addClass('disabled'); */


        $('#redownload-area').html(
            '<div class="fa-4x text-center" style="text-align: center;"><i class="fas fa-sync fa-spin" ></i></div>' +
            '<div class="text-center"><?php echo str_replace("'", "\'", localize("MODAL_REINSTALL_LOADING")); ?></div>'
        );


        $.ajax({
            url: '<?php echo QNAPDOCROOT;?>/qpkg/RoonServer/ajax/ajax.php?a=redownload',
            success: function () {
                /* $('#btn-close-redownload').prop("disabled", false);
                 $('#btn-close-redownload').removeClass('disabled');
                 $('#btn-x-redownload').prop("disabled", false);
                 $('#btn-x-redownload').removeClass('disabled'); */

                $('#redownload-area').html(
                    '<svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"><circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/><path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/></svg><br>' +
                    '<div class="text-center"><?php echo str_replace("'", "\'", localize("MODAL_REINSTALL_DONE")); ?></div>'
                );

            }
        });
    });

    $(function () {
        // Enable Tooltips
        $('[data-toggle="tooltip"]').tooltip()
    })

    $('.reinstall').click(function () {
        $.ajax({
            type: "POST",
            url: "ajax/ajax.php?a=reinstall"
        });
    });

    // check if selection is valid and enable/disable button


    function db_save_button() {
        var path = newdbpath;
        var action = 'updateformfield';
        var strUrl = '<?php echo QNAPDOCROOT;?>/qpkg/RoonServer/ajax/ajax.php?a=' + action + '&t=' + path;

        $.ajax({
            url: strUrl,
            dataType: 'json'
        });

        var dbexist = <?php if (isset($dblocation)) {
            echo "true";
        } else {
            echo "false";
        } ?>;

        console.log('New Path:' + newdbpath + ' --- Old Path: <?php echo $dblocation; ?>')

        var olddbpath = '<?php echo $dblocation; ?>';


        if (!dbexist) {
            selectStorageSuccess();
        }
        if (dbexist && newdbpath != olddbpath) {
            $('#modal-content-storage').html('<div class="modal-header">\n' +
                '<h4 class="modal-title"><?php echo str_replace("'", "\'", localize("MODAL_SETUP_RESTART_HEADLINE")); ?></h4>\n' +
                '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>\n' +
                '</div>\n' +
                '<div id="modal-body modal-body-storage" class="modal-body">\n' +
                '<?php echo str_replace("'", "\'", localize("MODAL_SETUP_RESTART_TEXT")); ?>' +
                '<a id="restartRoonServer" href="#" onclick="restart_roonserver()">\n' +
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
            $('#modal-content-storage').html('<div class="modal-header">\n' +
                '<h4 class="modal-title"><?php echo str_replace("'", "\'", localize("MODAL_SETUP_RESTART_SAME_PATH")); ?></h4>\n' +
                '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>\n' +
                '</div>\n' +
                '<div id="modal-body modal-body-storage" class="modal-body">\n' +
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
            //selectStorageSuccess();
        }

        /// Check if dblocation has changed and display a restart button.

    }


    function restart_roonserver() {
        $.ajax({
            url: '<?php echo QNAPDOCROOT;?>/qpkg/RoonServer/ajax/ajax.php?a=restartRoonServer'
        });
        selectStorageSuccess();

    }

    function selectStorageSuccess() {
        var checkani = "<svg class=\"checkmark\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 52 52\"><circle class=\"checkmark__circle\" cx=\"26\" cy=\"26\" r=\"25\" fill=\"none\"/><path class=\"checkmark__check\" fill=\"none\" d=\"M14.1 27.2l7.1 7.2 16.7-16.8\"/></svg>";

        $('#modal-content-storage').html(checkani + '<div class="roon-template"><h4><?php echo str_replace("'", "\'", localize("MODAL_SETUP_BTN_LOCATION_SAVED")); ?></h4></div>');

        $.ajax({
            url: '<?php echo QNAPDOCROOT;?>/qpkg/RoonServer/ajax/ajax.php?a=startRoonServer'
        });
    }
</script>