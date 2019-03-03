<?php
if (!isset($_COOKIE['as_sid']) && ($_COOKIE['as_logout'] == "1") || empty($_COOKIE['as_sid'])) {
    die("not logged in! ;)");
}

    include_once("../__include.php");
    include_once("../__functions.php");
?>


<script type="text/javascript">
//    var json_obj = '<?php echo json_encode(getTreeRoot($strSessionID)); ?>';
    var json_obj = JSON.parse('<?php getFoldersAt("/share"); ?>');

   // console.log(json_obj);
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

var tree;
tree = $('#tree').tree({
    dataSource: json_obj,
    iconsLibrary: 'fontawesome',
    hasChildrenField: 'anychildren',
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
    $('#dblocform').val(newdbpath.substring(6));
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
        var strUrl = '/RoonServer/ajax/ajax.php?a=' + action + '&t=' + path;

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
