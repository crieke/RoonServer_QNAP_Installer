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
        <!-- <h1><?php echo localize("OVERVIEW_HEADLINE"); ?></h1>
        <p class="lead"><?php echo localize("OVERVIEW_TEXT1"); ?><br>
            <?php echo localize("OVERVIEW_TEXT2"); ?></p>-->
        <div class="row">
            <div class="col-sm-8">
                <div class="card">
                    <div class="card-body">
                        <span class="fa-stack fa-2x">
                            <i class="fa fa-circle fa-stack-2x" style="color: #222222;"></i>
                            <img src="img/roonIcon.svg" alt="Roon Icon" style="height: 35px;" class="fa-stack-1x fa-inverse">
                        </span>
                        <h5 class="card-title">Roon Server</h5>
                        <p class="card-text">
                            <b><?php echo localize("OVERVIEW_ROONSERVER_PANEL_STATUS"); ?>
                                :</b> <?php if (strlen(isRunning()) > 1 ) {
                                echo '<span data-bs-toggle="tooltip" data-bs-title="' . localize("OVERVIEW_ROONSERVER_PANEL_CONTAINER_ID") .': ' . isRunning() . '" style="color: green;">' . localize("OVERVIEW_ROONSERVER_PANEL_STATUS_RUNNING") . '</span>';
                            } else {
                                echo '<span style="color: red;">' . localize("OVERVIEW_ROONSERVER_PANEL_STATUS_STOPPED") . '</span>';
                            } ?><br>
                            <b><?php echo localize("OVERVIEW_ROONSERVER_PANEL_VERSION"); ?>
                                :</b> <?php echo getRoonServerVersion()[1]; ?><br>
                            <b><?php echo localize("OVERVIEW_ROONSERVER_PANEL_QPKG_VERSION"); ?>
                                :</b> <?php echo $qpkg_conf['RoonServer']['Version']; ?></span>
                        </p>
                            <h5><?php echo localize("OVERVIEW_ROONSERVER_PANEL_SUBHEAD_DATABASE"); ?></h5>
                            <span data-bs-toggle="tooltip" data-bs-title="<?php echo $dblocation; ?>">
                                <b><?php echo localize("OVERVIEW_ROONSERVER_PANEL_LOCATION"); ?>: </b><?php echo $dblocation; ?>
                            </span>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar" role="progressbar" style="width: <?php echo $db_perc ?>%;" aria-valuenow="<?php echo(100 - $db_perc) ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <p>
                            <?php echo $db_perc . '% ' . localize("OVERVIEW_ROONSERVER_PANEL_SPACE_OF") . ' ' . displayStorage($db_vol_cap) . ' ' . localize("OVERVIEW_ROONSERVER_PANEL_SPACE_USED") . '.'; ?>
                        </p>
                        <div class="row">
                            <span id="log" class="col-2 getModal d-flex justify-content-start">
                                <a href="#"
                                class="btn btn-outline-danger"
                                data-bs-theme="dark"
                                data-bs-toggle="tooltip"
                                data-bs-title="<?php echo localize("MODAL_LOGFILES_ICON_TOOLTIP"); ?>">
                                    <i class="fas fa-ambulance"></i>
                                </a>
                            </span>
                            <span id="setStorage" class="col-9 getModal ms-auto float-end">
                                <a href="#"
                                class="btn btn-primary"
                                data-bs-theme="dark"
                                data-bs-toggle="tooltip"
                                data-bs-title="<?php echo localize("OVERVIEW_ROONSERVER_PANEL_CHANGE_DB_LOCATION_TOOLTIP"); ?>">
                                    <?php echo localize("OVERVIEW_ROONSERVER_PANEL_CHANGE_DB_LOCATION"); ?>
                                </a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card">
                    <div class="card-body">
                        <span class="fa-stack fa-2x">
                            <i class="fa fa-circle fa-stack-2x" style="color: #222222;"></i>
                            <i class="fas fa-cog fa-stack-1x" style="color: #ffffff;"></i>
                        </span>
                        <h5><?php echo localize("OVERVIEW_OPTIONS_PANEL_TITLE"); ?></h5>

                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="smb_cifs" onchange="changeSettings(this)">
                                <label class="form-check-label justify-content-start" for="flexSwitchCheckChecked">SMB/CIFS mount support</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="usb_audio" onchange="changeSettings(this)">
                                <label class="form-check-label justify-content-start" for="flexSwitchCheckChecked" data-bs-toggle="tooltip" data-bs-title="<?php echo localize("OVERVIEW_OPTIONS_PANEL_USB_AUDIO_TOOLTIP"); ?>">USB audio (DAC)</label>
                            </div>
                            
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="hdmi_audio" onchange="changeSettings(this)">
                                <label class="form-check-label justify-content-start text-left" for="flexSwitchCheckChecked">HDMI audio</label>
                            </div>
                            
                        <span id="save" class="getModal">
                            <a href="#"
                               id="saveButton"
                               class="btn btn-primary float-right disabled"
                               data-bs-toggle="tooltip"
                               onclick="saveOptions()"
                               data-bs-title="Save & Restart"
                               data-bs-theme="dark"
                               ><?php echo localize("OVERVIEW_OPTIONS_PANEL_SAVE_BTN"); ?>
                            </a>
                        </span>
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

    // Action when button for Modal is clicked
    $('.getModal').on('click', function (e) {

        // Hide Tooltips when modal opens
        $(function () {
            $('[data-bs-toggle="tooltip"]').tooltip('dispose')
        });

        // Check which button pressed
        e.preventDefault();
        var modalContent = $(this).attr('id');
        $('#modal-content').load("modals.php?s=" + modalContent);

        // Request Modal with content
        $('#modal').modal('show');
        return false;
    });
    function changeSettings() {
        var qpkg_options_str = "<?php echo $qpkg_conf_options ?>";
        var qpkg_options_arr = qpkg_options_str.split(' ');
        
        var qnap_opt_arr = [];
        document.getElementById('smb_cifs').checked && qnap_opt_arr.push("smb_cifs");
        document.getElementById('usb_audio').checked && qnap_opt_arr.push("usb_audio");
        document.getElementById('hdmi_audio').checked && qnap_opt_arr.push("hdmi_audio");
        
        console.log(qnap_opt_arr.join(' ') == qpkg_options_arr.join(' '));
        
            console.log(qnap_opt_arr.join(' ') == qpkg_options_arr.join(' '));
        
        if ( qnap_opt_arr.join(' ') == qpkg_options_arr.join(' ') ) {
            $("#saveButton").addClass("disabled");
        } else {
            $("#saveButton").removeClass("disabled");
        }        
    }
    
 $( document ).ready(function() {
     var qpkg_options_str = "<?php echo $qpkg_conf_options ?>";
     
     const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
     const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
     
     if ( qpkg_options_str.length > 0 ) {
        var qpkg_options_arr = qpkg_options_str.split(' ');
        
        for (let conf_option of qpkg_options_arr) {
            document.getElementById(conf_option).checked = true;
        }
    }
});


// Function to download log files
function saveOptions () {
        var qnap_options = "";
        qnap_options += document.getElementById('smb_cifs').checked ? "smb_cifs;" : "" ;
        qnap_options += document.getElementById('usb_audio').checked ? "usb_audio;" : "" ;
        qnap_options += document.getElementById('hdmi_audio').checked ? "hdmi_audio;" : "" ;
        
        $("#saveButton").addClass("disabled");

        document.getElementById('smb_cifs').checked 
        var strUrl = '<?php echo NASHOST;?>/cgi-bin/qpkg/RoonServer/ajax/ajax.php?a=setOptions&o=' + qnap_options;
     
        $.ajax({
            url: strUrl,
            dataType: 'json',
            success: function (cb_data) {
                restartRoonServer();
            }
        });
    }

</script>