<?php
if (!isset($_COOKIE['NAS_USER']) || empty($_COOKIE['NAS_USER'])) {
    die("not logged in! ;)");
}
?>

<div class="modal-header">
    <h4 id="modal-title" class="modal-title"><?php echo localize("MODAL_ABOUT_HEADLINE"); ?></h4>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<div id="modal-body" class="modal-body modal-content">


    <div class="container">
        <div class="roon-template">
        </div>
        Copyright 2016-2018 Christopher Rieke<br><br>

        <br><br>

        <h4><?php echo localize("MODAL_ABOUT_THANKS"); ?>:</h4><br>
        <div id="contrib"></div>

        <script>
            $(document).ready(function () {
                $('#contrib').html('<div class="container h-100"> <div class="row h-100 justify-content-center align-items-center"><i class="fas fa-sync fa-spin fa-5x"></i></div></div>');

                var repo = 'https://api.github.com/repos/crieke/RoonServer_QNAP_Installer/contributors';

                requestJSON(repo, function (json) {
                    var outhtml = '<ul class=\"media-list\">';

                    jQuery.each(json, function (i, obj) {
                        // else we have a user and we display their info
                        var username = obj.login;
                        var aviurl = obj.avatar_url;
                        var userprofile = obj.html_url;
                        var contr = obj.contributions;

                        outhtml = outhtml + '<li id="contriblist" class="media contriblist">';
                        outhtml = outhtml + '<span class="pull-left">';
                        outhtml = outhtml + '<a href="' + userprofile + '" target="_blank">';
                        outhtml = outhtml + '<img class="media-object d-block" style="max-height: 80px" src="' + aviurl + '" alt="...">';
                        outhtml = outhtml + '</a>';
                        outhtml = outhtml + '</span>';
                        outhtml = outhtml + '<div class="media-body" style="padding-left: 15px">';
                        outhtml = outhtml + '<a href="' + userprofile + '" target="_blank">';
                        outhtml = outhtml + '<h4 class="media-heading">' + username + '</h4>';
                        outhtml = outhtml + '</a>';
                        outhtml = outhtml + '<p> Contributions: ' + contr + '</p>';
                        outhtml = outhtml + '</div>';
                        outhtml = outhtml + '</li>';
                    }); // end each
                    outhtml = outhtml + '</ul>';

                    function outputPageContent() {
                        $('#contrib').html(outhtml);
                    } // end outputPageContent()
                    outputPageContent();

                }); // end requestJSON Ajax call
            }); // end click event handler
            function requestJSON(url, callback) {
                $.ajax({
                    url: url,
                    complete: function (xhr) {
                        callback.call(null, xhr.responseJSON);
                    }
                });
            }
        </script>

        <br><br>
        <?php
        $ContributorsManual = array(
            array(
                'username' => 'RoonLabs',
                'aviurl' => 'https://avatars2.githubusercontent.com/u/15744118?v=4',
                'userprofile' => 'https://github.com/RoonLabs',
                'contr' => 'Thanks for the continuous support and help.'
            ),
            array(
                'username' => 'QNAP',
                'aviurl' => 'https://www.qnap.com/i/images/q_logo.jpg',
                'userprofile' => 'https://www.qnap.com',
                'contr' => ''
            ),
            array(
                'username' => 'marianoglas',
                'aviurl' => 'https://avatars0.githubusercontent.com/u/5198307?v=4',
                'userprofile' => 'https://github.com/marianoglas',
                'contr' => 'Thanks for your help with the API and some AJAX.'
            ),
            array(
                'username' => 'Ignaas Vanden Poel'
            ),
            array(
                'username' => 'Albin Johansson',
                'userprofile' => 'http://www.albinjo.com'

            ),
            array(
                'username' => 'Estefania San Lorenzo'
            )
        );

        $manualContrList = '<ul class="media-list">';
        foreach ($ContributorsManual as $singleContr) {

            // Generate a thumbnail if no aviurl is specified

            if (!array_key_exists('aviurl', $singleContr)) {
                $singleContr['aviurl'] = 'assets/identicon.php?string=' . $singleContr['username'];
            }

            $hasLink = array_key_exists('userprofile', $singleContr);
            $hasDesc = array_key_exists('contr', $singleContr);


            $manualContrList = $manualContrList . '<li id="contriblist" class="media contriblist">';
            $manualContrList = $manualContrList . '<span class="pull-left">';
            if ($hasLink) { $manualContrList = $manualContrList . '<a href="' . $singleContr['userprofile'] . '" target="_blank">';}
            $manualContrList = $manualContrList . '<img class="media-object d-block" style="max-height: 80px" src="' . $singleContr['aviurl'] . '" alt="...">';
            if ($hasLink) {$manualContrList = $manualContrList . '</a>';}
            $manualContrList = $manualContrList . '</span>';
            $manualContrList = $manualContrList . '<div class="media-body" style="padding-left: 15px">';
            if ($hasLink) {$manualContrList = $manualContrList . '<a href="' . $singleContr['userprofile'] . '" target="_blank">';}
            $manualContrList = $manualContrList . '<h4 class="media-heading">' . $singleContr['username'] . '</h4>';
            if ($hasLink) {$manualContrList = $manualContrList . '</a>';}
            if ($hasDesc) {$manualContrList = $manualContrList . '<p>' . $singleContr['contr'] . '</p>';}
            $manualContrList = $manualContrList . '</div>';
            $manualContrList = $manualContrList . '</li>';
        }
        $manualContrList = $manualContrList . '</ul>';


        echo '<h4>' . localize("MODAL_ABOUT_SPECIALTHANKS") . ':</h4><br>  ';

        echo $manualContrList; ?>
        <br>

        <?php
        // USED ASSETS AND LIBRARIES
        echo localize("MODAL_ABOUT_ASSETS"); ?><br><br>

        <ul class="list-group list-group-flush">

            <?php $assetList = array(
                array(
                    'assetName' => 'Bootstrap',
                    'author' => 'Twitter',
                    'href' => 'https://github.com/twbs/bootstrap',
                    'license' => 'MIT'
                ),
                array(
                    'assetName' => 'Popper.js',
                    'author' => 'Federico Zivolo',
                    'href' => 'https://github.com/FezVrasta/popper.js',
                    'license' => 'MIT'
                ),
                array(
                    'assetName' => 'Jquery',
                    'author' => 'JS Foundation',
                    'href' => 'http://jquery.com',
                    'license' => 'MIT'
                ),
                array(
                    'assetName' => 'Gijgo Tree',
                    'author' => 'Atanas Atanasov',
                    'href' => 'https://github.com/atatanasov/gijgo',
                    'license' => 'MIT'
                ),
                array(
                    'assetName' => 'Fontawesome',
                    'author' => 'Fort Awesome',
                    'href' => 'https://fontawesome.com',
                    'license' => 'MIT, CC'
                ),
                array(
                    'assetName' => 'Fontawesome Animation',
                    'author' => 'Louis LIN',
                    'href' => 'https://github.com/l-lin/font-awesome-animation',
                    'license' => 'MIT'
                ),
                array(
                    'assetName' => 'jquery.fileDownload',
                    'author' => 'John Culviner',
                    'href' => 'https://github.com/johnculviner/jquery.fileDownload',
                    'license' => 'MIT'
                )
            );

            $usedAssets = '<ul class="media-list">';
            foreach ($assetList as $singleAsset) {
                $usedAssets = $usedAssets . '<li class="list-group-item"><b><a href="' . $singleAsset['href'] . '" target="_blank">' . $singleAsset['assetName'] . '</a></b> ' . localize("MODAL_ABOUT_BY") . ' ' . $singleAsset['author'] . '<br>' . localize("MODAL_ABOUT_LICENSE") . ': ' . $singleAsset['license'] . '</li>';
            }
            $usedAssets = $usedAssets . '</ul>';
            echo $usedAssets;
            ?>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal"><?php echo localize("BTN_CLOSE"); ?></button>
