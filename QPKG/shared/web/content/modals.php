<div class="modal fade" id="modal-storage" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" id="modal-content-storage" style="width: 600px;">
            <div class="modal-header">
                <h4 class="modal-title"><?php echo localize("MODAL_SETUP_HEADLINE"); ?></h4>
                <button type="button" class="close closemodal" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div id="modal-body modal-body-storage" class="modal-body">
                <p><?php include "content/setup.php" ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default closemodal" data-dismiss="modal"><?php echo localize("BTN_CLOSE"); ?>
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
                <button type="button" class="close closemodal" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div id="modal-body" class="modal-body">
                <p><?php include "content/about.php" ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default closemodal" data-dismiss="modal"><?php echo localize("BTN_CLOSE"); ?>
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
                <button type="button" class="close closemodal" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div id="modal-body" class="modal-body">
                <pre><?php echo $alsatext; ?></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default closemodal" data-dismiss="modal"><?php echo localize("BTN_CLOSE"); ?>
                </button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modal-redownload" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" id="databasemodal" style="width: 600px;">
            <div class="modal-header">
                <h4 class="modal-title"><?php echo localize("MODAL_REINSTALL_HEADLINE"); ?></h4>
                <button type="button" id="btn-x-redownload" class="close closemodal" data-dismiss="modal" aria-hidden="true">×</button>
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
                <button id="btn-close-redownload" type="button" class="btn btn-default closemodal" data-dismiss="modal"><?php echo localize("BTN_CLOSE"); ?>
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
                <button type="button" class="close closemodal" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div id="modal-body" class="modal-body">
                <p><?php echo localize("MODAL_LOGFILES_DESCRIPTION1"); ?><br><br>
                    <?php echo localize("MODAL_LOGFILES_DESCRIPTION2"); ?>
                </p>
                <span id="download-area">
                    <a id="downloadlogs" href="#>">
                        <div class="fa-4x text-center" style="text-align: center;">
                            <span class="fa-layers fa-fw">
                                <i class="fas fa-circle"></i>
                                <i class="fa-inverse fas fa-ambulance faa-passing animated" data-fa-transform="shrink-6"></i>
                            </span>
                        </div>
                        <div class="text-center">
                            <?php echo localize("MODAL_LOGFILES_DOWNLOAD_BTN_TEXT"); ?>
                        </div>
                    </a>
                    </span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default closemodal" data-dismiss="modal"><?php echo localize("BTN_CLOSE"); ?>
                </button>
            </div>
        </div>
    </div>
</div>