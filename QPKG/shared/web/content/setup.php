<?php
if (!isset($_COOKIE['NAS_USER']) || empty($_COOKIE['NAS_USER'])) {
    die("not logged in! ;)");
}
?>
<script type="text/javascript">
    var json_obj = <?php echo json_encode(getTreeRoot($strSessionID)); ?>;

</script>
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
            primaryKey: 'id',
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
            $('#dblocform').val('<?php if (!is_null($dblocation)) {echo $dblocation;} else {echo localize("MODAL_SETUP_FORM_NO_FOLDER_SELECTED");} ?>');
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
                var strUrl = '<?php echo QNAPDOCROOT;?>/qpkg/RoonServer/ajax/ajax.php?a=' + action + '&t=' + path;

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

        // check if selection is valid and enable/disable button


        function db_save_button() {
            var path = newdbpath;
            var action = 'updateformfield';
            var strUrl = '<?php echo QNAPDOCROOT;?>/qpkg/RoonServer/ajax/ajax.php?a=' + action + '&t=' + path;

            $.ajax({
                url: strUrl,
                dataType: 'json'
            });

            var dbexist = <?php if (isset($dblocation)) {echo "true";} else {echo "false";} ?>;

            console.log('New Path:' + newdbpath + ' --- Old Path: <?php echo $dblocation; ?>')

            var olddbpath = '<?php echo $dblocation; ?>';



            if (!dbexist) {
                selectStorageSuccess();
            }
            if ( dbexist && newdbpath != olddbpath ) {
                $('#modal-content-storage').html('<div class="modal-header">\n' +
                                                    '<h4 class="modal-title"><?php echo localize("MODAL_SETUP_RESTART_HEADLINE"); ?></h4>\n' +
                                                    '<button type="button" class="close closemodal" data-dismiss="modal" aria-hidden="true">×</button>\n' +
                                                '</div>\n' +
                                                    '<div id="modal-body modal-body-storage" class="modal-body">\n' +
                                                        '<?php echo localize("MODAL_SETUP_RESTART_TEXT"); ?>' +
                                                        '<a id="restartRoonServer" href="#" onclick="restart_roonserver()">\n' +
                                                            '<div class="fa-4x text-center" style="text-align: center;">\n' +
                                                                '<span class="fa-layers fa-fw">\n' +
                                                                    '<i class="fas fa-circle"></i>\n' +
                                                                    '<i class="fa-inverse fas fa-redo-alt faa-shake animated" data-fa-transform="shrink-8"></i>\n' +
                                                                '</span>\n' +
                                                            '</div>\n' +
                                                            '<div class="text-center">\n' +
                                                                '<?php echo localize("MODAL_SETUP_RESTART_ROONSERVER"); ?>\n' +
                                                            '</div>\n' +
                                                        '</a>\n' +
                                                    '</div>\n');
            }
            else
            {
                $('#modal-content-storage').html('<div class="modal-header">\n' +
                    '<h4 class="modal-title"><?php echo localize("MODAL_SETUP_RESTART_SAME_PATH"); ?></h4>\n' +
                    '<button type="button" class="close closemodal" data-dismiss="modal" aria-hidden="true">×</button>\n' +
                    '</div>\n' +
                    '<div id="modal-body modal-body-storage" class="modal-body">\n' +
                    '<?php echo localize("MODAL_SETUP_RESTART_SAME_PATH_TEXT"); ?>' +
                    '<a id="restartRoonServer" href="#" class="closemodal" onclick="close_modal_pageReload()" data-dismiss="modal">\n' +
                    '<div class="fa-4x text-center" style="text-align: center;">\n' +
                    '<span class="fa-layers fa-fw">\n' +
                    '<i class="fas fa-exclamation-circle faa-shake animated"></i>\n' +
                    '</span>\n' +
                    '</div>\n' +
                    '<div class="text-center">\n' +
                    '<?php echo localize("BTN_CLOSE"); ?>\n' +
                    '</div>\n' +
                    '</a>\n' +
                    '</div>\n');
                //selectStorageSuccess();
            }

            /// Check if dblocation has changed and display a restart button.

        }

        function close_modal_pageReload () {
            setTimeout(function () {
                location.reload();
            }, 500);
        }

        function restart_roonserver() {
            $.ajax({
                url: '<?php echo QNAPDOCROOT;?>/qpkg/RoonServer/ajax/ajax.php?a=restartRoonServer'
            });
            selectStorageSuccess();

        }

        function selectStorageSuccess() {
            var checkani = "<svg class=\"checkmark\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 52 52\"><circle class=\"checkmark__circle\" cx=\"26\" cy=\"26\" r=\"25\" fill=\"none\"/><path class=\"checkmark__check\" fill=\"none\" d=\"M14.1 27.2l7.1 7.2 16.7-16.8\"/></svg>";

            setTimeout(function () {
                $('#modal-storage').modal('hide');
            }, 3000);

            $('#modal-content-storage').html(checkani + '<div class="roon-template"><h4><?php echo localize("MODAL_SETUP_BTN_LOCATION_SAVED"); ?></h4></div>');

            $.ajax({
                url: '<?php echo QNAPDOCROOT;?>/qpkg/RoonServer/ajax/ajax.php?a=startRoonServer'
            });

            setTimeout(function () {

                $('#contentblock').load('<?php echo QNAPDOCROOT;?>/qpkg/RoonServer/content/info.php');
            }, 2000);
        }

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
                if (!is_null($dblocation)) {
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
