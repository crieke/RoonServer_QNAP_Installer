<?php
if (!isset($_COOKIE['as_sid']) && ($_COOKIE['as_logout'] == "1") || empty($_COOKIE['as_sid'])) {
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

