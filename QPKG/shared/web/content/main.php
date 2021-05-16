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
    if ( (false === $xml) || !array_key_exists('authPassed', $xml) || !array_key_exists('username', $xml))
    {
        die('Could not verify session id.');
    }
    if ( !(bool)(int)$xml->authPassed[0] || (string)$xml->username[0] !== $_COOKIE['NAS_USER'])
    {
        die('No authentic session id!');
    }
} else { 
    die('Not logged in!');
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
        <button type="button" id="setStorage" class="getModal btn btn-primary"><?php echo localize("SETUP_BTN_CONFIGURE"); ?>
        </button>
    </div>
</div>

<script>
    var dbexist = <?php if (isset($dblocation)) {
        echo "true";
    } else {
        echo "false";
    } ?>;

    // Action when button for Modal is clicked
    $('.getModal').on('click', function(e) {

        // Check which button fired
        e.preventDefault();
        var modalContent = $(this).attr('id');

        $('#modal-content').load("modals.php?s=" + modalContent);
        //$('#modal-body').load("modals.php?s=" + modalContent + "&r=DESCRIPTION");

        // Request Modal with content


        //Open Modal
        $('#modal').modal('show');
        return false;
    });
</script>

