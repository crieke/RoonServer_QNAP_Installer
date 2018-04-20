<?php
if (!isset($_COOKIE['NAS_USER']) || empty($_COOKIE['NAS_USER'])) {
    die("not logged in! ;)");
}
?>
<div id="contentblock" class="container">
    <div class="roon-template">
        <span class="fa-stack fa-8x">
            <i class="fa fa-circle fa-stack-1x" style="color: #222222;"></i>
            <img src="img/roonIcon.svg" style="height: 70px;" ,
                 class="fa-stack-2x fa-inverse"/>
        </span>

        <h1><?php echo localize("SETUP_HEADLINE"); ?></h1>
        <p class="lead"><?php echo localize("SETUP_DESCRIPTION_1"); ?><br><?php echo localize("SETUP_DESCRIPTION_2"); ?></p>
        <button type="button" class="btn btn-primary" data-toggle="modal"
                data-target="#modal-storage"><?php echo localize("SETUP_BTN_CONFIGURE"); ?>
        </button>
    </div>
</div>

