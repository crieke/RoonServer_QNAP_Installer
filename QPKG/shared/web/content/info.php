<?php
if (isset($_COOKIE['NAS_USER']) && isset($_COOKIE['NAS_SID'])) {
    $context = stream_context_create(array('ssl'=>array(
    'verify_peer' => false, 
    "verify_peer_name"=>false
)));
    libxml_set_streams_context($context);
    $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://127.0.0.1:$_SERVER[SERVER_PORT]/cgi-bin/authLogin.cgi?sid=".$_COOKIE['NAS_SID'];
    $xml = simplexml_load_file($url);
    unset($context);
    if ( (false === $xml) || !isset($xml->authPassed) || !isset($xml->username) || !isset($xml->isAdmin) ) {
        die('Unable to retrieve xml authentication info from your qnap device.');
    }
    if ( !(bool)(int)$xml->authPassed[0] || !(bool)(int)$xml->isAdmin[0] || (string)$xml->username[0] !== $_COOKIE['NAS_USER']) {
        die('No authentic session id of an admin user!');
    }
} else {
    die('Not logged in!');
}

include_once("/home/httpd/cgi-bin/qpkg/RoonServer/__include.php");
include_once("/home/httpd/cgi-bin/qpkg/RoonServer/__functions.php");
?>

<div class="container">
    <div class="roon-template">
        <h1><?php echo localize("OVERVIEW_HEADLINE"); ?></h1>
        <p class="lead"><?php echo localize("OVERVIEW_TEXT1"); ?><br>
            <?php echo localize("OVERVIEW_TEXT2"); ?></p>
        <div class="row">
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <span class="fa-stack fa-2x">
                            <i class="fa fa-circle fa-stack-2x" style="color: #222222;"></i>
                            <img src="img/roonIcon.svg" alt="Roon Icon" style="height: 35px;" class="fa-stack-1x fa-inverse">
                        </span>
                        <h5 class="card-title">Roon Server</h5>
                        <p class="card-text">
                            <b><?php echo localize("OVERVIEW_ROONSERVER_PANEL_STATUS"); ?>
                                :</b> <?php if (isRunning()) {
                                echo '<span data-toggle="tooltip" title="' . localize("OVERVIEW_ROONSERVER_PANEL_PID") .': ' . isRunning("getpid") . '" style="color: green;">' . localize("OVERVIEW_ROONSERVER_PANEL_STATUS_RUNNING") . '</span>';
                            } else {
                                echo '<span style="color: red;">' . localize("OVERVIEW_ROONSERVER_PANEL_STATUS_STOPPED") . '</span>';
                            } ?><br>
                            <b><?php echo localize("OVERVIEW_ROONSERVER_PANEL_VERSION"); ?>
                                :</b> <?php echo $RoonVersion[1]; ?><br>
                            <b><?php echo localize("OVERVIEW_ROONSERVER_PANEL_QPKG_VERSION"); ?>
                                :</b> <?php echo $qpkg_conf['RoonServer']['Version']; ?><br>
                            <b><?php echo "ffmpeg"; ?>
                                :</b>
                                <span id="ffmpeg" class="getModal" data-toggle="tooltip" title="<?php echo localize("OVERVIEW_ROONSERVER_PANEL_VERSION");?>: <?php echo $ffmpegVersion; ?>"><button class="btn btn-xs btn-outline-dark"><i style="color:#aaaaaa;" class="fas fa-cog"></i> <?php echo $customFfmpeg ? localize('MODAL_FFMPEG_USER_SUPPLIED_VERSION') : localize('MODAL_FFMPEG_SYSTEM_DEFAULT'); ?></button></span>
                        </p>
                            <h5><?php echo localize("OVERVIEW_ROONSERVER_PANEL_SUBHEAD_DATABASE"); ?></h5>
                        <p>
                            <span data-toggle="tooltip" title="<?php echo $dblocation; ?>">
                                <b><?php echo localize("OVERVIEW_ROONSERVER_PANEL_LOCATION"); ?>: </b><?php echo $dblocation; ?>
                            </span>
                            <br>
                        </p>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar" role="progressbar" style="width: <?php echo $db_perc ?>%;" aria-valuenow="<?php echo(100 - $db_perc) ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <p>
                            <?php echo $db_perc . '% ' . localize("OVERVIEW_ROONSERVER_PANEL_SPACE_OF") . ' ' . displayStorage($db_vol_cap) . ' ' . localize("OVERVIEW_ROONSERVER_PANEL_SPACE_USED") . '.'; ?>
                        </p>
                        <span id="log" class="getModal">
                            <a href="#"
                               class="btn btn-light btn-icon float-left"
                               data-toggle="tooltip"
                               title="<?php echo localize("MODAL_LOGFILES_ICON_TOOLTIP"); ?>">
                                <i class="fas fa-ambulance"></i>
                            </a>
                        </span>
                        <span id="setStorage" class="getModal">
                            <a href="#"
                               class="btn btn-primary float-right"
                               data-toggle="tooltip"
                               title="<?php echo localize("OVERVIEW_ROONSERVER_PANEL_CHANGE_DB_LOCATION_TOOLTIP"); ?>">
                                <?php echo localize("OVERVIEW_ROONSERVER_PANEL_CHANGE_DB_LOCATION"); ?>
                            </a>
                        </span>
                        <span id="reinstall" class="getModal">
                            <a href="#"
                               class="btn btn-light btn-icon float-left"
                               data-toggle="tooltip"
                               title="<?php echo localize("MODAL_REINSTALL_ICON_TOOLTIP"); ?>">
                                    <i class="fas fa-box-open"></i>
                            </a>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <?php if (!$multimediaDisabled) {
                            echo '<span class="fa-stack fa-2x">' .
                                '<i class="fa fa-circle fa-stack-2x" style="color: #222222;"></i>' .
                                '<i class="fas fa-volume-up fa-stack-1x fa-inverse"></i>' .
                                '</span>';
                        } else {
                            echo '<span class="fa-stack fa-2x">' .
                                '<i class="fa fa-circle fa-stack-2x" style="color: #dddddd;"></i>' .
                                '<i class="fas fa-volume-off fa-stack-1x fa-inverse" data-fa-transform="left-3"></i>' .
                                '<i class="fas fa-times fa-stack-1x fa-inverse" data-fa-transform="shrink-10 right-5" ></i>' .
                                '</span>';
                        }
                        ?>

                        <h5 class="card-title"><?php
                            if (!$multimediaDisabled) {
                                echo localize("OVERVIEW_AUDIO_PANEL_HEADLINE");
                            } else {
                                echo localize("OVERVIEW_AUDIO_PANEL_NO_MULTIMEDIA_HEADLINE");
                            }
                            ?></h5>
                            <?php

                            if (!$multimediaDisabled) {
                                if (!$multimedia_disabled_on_start) {
                                    echo '<ul class="list-group">' .
                                        acardsNice() .
                                        '</ul><br>' .
                                        '<span id="alsa" class="getModal">' .
                                        '<a href="#" ' .
                                        'class="btn btn-primary" ' .
                                        'data-toggle="tooltip" ' .
                                        'data-html="true" ' .
                                        'title="' . localize("OVERVIEW_AUDIO_PANEL_BTN_AUDIO_DEVICES_TOOLTIP") . '">' .
                                        '<i class="fas fa-eye"></i> ' . localize("OVERVIEW_AUDIO_PANEL_BTN_AUDIO_DEVICES") .
                                        '</a>' .
                                        '</span>';
                                } else {
                                    echo localize("OVERVIEW_AUDIO_PANEL_NO_MULTIMEDIA_DESCRIPTION2");
                                    echo '<div id="restartRoonServerAudioPanel">'.
                                            '<a id="restartRoonServer" href="#" onclick="restartRoonServerAndRefresh()"></p>' .
                                                '<div class="fa-4x text-center" style="text-align: center;"><p>' .
                                                    '<span class="fa-layers fa-fw">' .
                                                        '<i class="fas fa-circle"></i>' .
                                                        '<i class="fa-inverse fas fa-redo-alt faa-shake animated" data-fa-transform="shrink-8"></i>' .
                                                    '</span></p>' .
                                                '</div>' .
                                                '<div class="text-center">' .
                                                    str_replace("'", "\'", localize("MODAL_SETUP_RESTART_ROONSERVER")) .
                                                '</div>' .
                                            '</a>'.
                                        '</div>';
                                }
                            } else {
                                echo localize("OVERVIEW_AUDIO_PANEL_NO_MULTIMEDIA_DESCRIPTION1");
                            } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var dbexist = <?php if (isset($dblocation)) {
        echo "true";
    } else {
        echo "false";
    } ?>;

    // Enable Tooltips
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    });

    // Action when button for Modal is clicked
    $('.getModal').on('click', function (e) {

        // Hide Tooltips when modal opens
        $(function () {
            $('[data-toggle="tooltip"]').tooltip('dispose')
        });

        // Check which button fired
        e.preventDefault();
        var modalContent = $(this).attr('id');
        $('#modal-content').load("modals.php?s=" + modalContent);

        // Request Modal with content
        $('#modal').modal('show');
        return false;
    });
</script>