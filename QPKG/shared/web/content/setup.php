<?php
if (isset($_COOKIE['NAS_USER']) && isset($_COOKIE['NAS_SID'])) {
    $context = stream_context_create(array('ssl'=>array(
    'verify_peer' => false, 
    'verify_peer_name' => false
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

<script type="text/javascript">
    var json_obj = <?php echo json_encode(getTreeRoot($strSessionID)); ?>;

</script>

<div class="modal-header">
    <h4 id="modal-title" class="modal-title"><?php echo localize("MODAL_SETUP_HEADLINE"); ?></h4>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<div id="modal-body" class="modal-body modal-content">

    <div class="container">

        <!-- Placed at the end of the document so the pages load faster -->
        <div class="container-fluid">
            <div id="tree" style="overflow-y:scroll;"></div>
        </div>

        <script>
            var newdbpath = "";

            function getCookie(name) {
                var value = "; " + document.cookie;
                var parts = value.split("; " + name + "=");
                if (parts.length == 2) return parts.pop().split(";").shift();
            }

            var tree;
            tree = $('#tree').tree({
                dataSource: json_obj,
                hasChildrenField: 'anychildren',
                iconsLibrary: 'fontawesome',
                imageCssClassField: 'faCssClass',
                uiLibrary: 'bootstrap4',
                icons: {
                    expand: '<i class="fas fa-angle-right" />',
                    collapse: '<i class="fas fa-angle-down" />'
                }
            });


            tree.on('select', function (e, node, id) {
                var nodeData = tree.getDataById(id);
                newdbpath = nodeData['path'];
                $('#dblocform').val(newdbpath);
                $('#btn_save').prop("disabled", false);
                $('#btn_save').removeClass('disabled');
            });

            tree.on('unselect', function () {
                $('#dblocform').val('<?php if (isset($dblocation)) {
                    echo $dblocation;
                } else {
                    echo localize("MODAL_SETUP_FORM_NO_FOLDER_SELECTED");
                } ?>');
                $('#btn_save').prop("disabled", true);
                $('#btn_save').addClass('disabled');
            });

            // Actions when expanding an item
            tree.on('expand', function (e, node, id) {
                var nodeData = tree.getDataById(id);
                anychildren = nodeData['anychildren'];


                if (anychildren) {

                    var path = encodeURIComponent(nodeData['path']);
                    var action = 'gettree';
                    var strUrl = '<?php echo NASHOST;?>/cgi-bin/qpkg/RoonServer/ajax/ajax.php?a=' + action + '&t=' + path;

                    $.ajax({
                        url: strUrl,
                        dataType: 'json',
                        success: function (cb_data) {
                            nodechildren = tree.getChildren(node);
                            nodechildren.forEach(function (entry) {
                                tree.removeNode(entry);
                            });


                            cb_data.forEach(function (entry) {
                                tree.addNode(entry, node);
                            });

                        }
                    });
                }
            });
        </script>


        <div class="row">
            <div class="col-lg-offset-3 col-lg-12">
                <div class="input-group">
                    <span class="input-group-btn">
                        <span class="form form-control"
                              style="background: #F5F5F5; border-radius: 0.25em 0 0 0.25em !important;"
                              readonly><?php echo localize("MODAL_SETUP_DB_LOCATION"); ?></span>
                        </span>
                    <input id="dblocform" type="text" class="form-control" style="background: #ffffff;" value="<?php
                    if (isset($dblocation)) {
                        echo $dblocation;
                    } else {
                        echo localize("MODAL_SETUP_FORM_NO_FOLDER_SELECTED");
                    } ?>" readonly>
                    <span class="input-group-btn">
                        <button id="btn_save" class="btn btn-secondary disabled" onclick="db_save_button()"
                                disabled="disabled" formmethod="post" type="submit"
                                style="background: #007bff;"><?php echo localize("BTN_SAVE"); ?>

                        </button>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal"><?php echo localize("BTN_CLOSE"); ?></button>
