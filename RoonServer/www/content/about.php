<?php
if (!isset($_COOKIE['as_sid']) && ($_COOKIE['as_logout'] == "1") || empty($_COOKIE['as_sid'])) {
    die("not logged in! ;)");
}

$ContributorsManual = array(
    array(
        'login' => 'RoonLabs',
        'avatar_url' => 'https://avatars2.githubusercontent.com/u/15744118?v=4',
        'html_url' => 'https://github.com/RoonLabs',
        'description' => 'For the continuous support and help.'
    ),
    array(
        'login' => 'marianoglas',
        'avatar_url' => 'https://avatars0.githubusercontent.com/u/5198307?v=4',
        'html_url' => 'https://github.com/marianoglas',
        'description' => 'For your help with the API and AJAX.'
    ),
    array(
        'login' => 'Ignaas Vanden Poel',
        'description' => 'Dutch translation'
    ),
    array(
        'login' => 'Aldewin Bedoya',
        'description' => 'Spanish translation'
    )


);
?>

<div class="modal-header">
    <h4 id="modal-title" class="modal-title"><?php echo localize("MODAL_ABOUT_HEADLINE"); ?></h4>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<div id="modal-body" class="modal-body modal-content">


    <div class="container">
        <div id="cr">
            <img src="img/cr_logo.svg" width="70px" height="70px"><br>
            Copyright 2016-2019 Christopher Rieke<br>
            <a href="https://roononnas.org" target="_blank">roononnas.org</a>
        </div><br><br>

        <div style="border: 5px; padding: 10px; text-align: center; border-style: dotted;">
            <?php echo localize("MODAL_ABOUT_COFFEE"); ?>
            <div style="text-align: center; font-size: 50px;">
                  <span class="fa-layers fa-fw">
                    <i class="fas fa-coffee" data-fa-transform="shrink-8" data-fa-mask="fas fa-circle"></i>
                    <i class="far fa-heart" style="color: #000000" data-fa-transform="shrink-12.5w left-0.5 up-0.8"></i>
                  </span>
            </div>

            <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
            <div style="text-align: center;">
                <input type="hidden" name="cmd" value="_s-xclick">
                <input type="hidden" name="hosted_button_id" value="27D9FUZPC3JNC">
                <input type="hidden" name="lc" value="<?php echo substr(localize("SYSTEM_LOCALE"), -2); ?>">
                <input type="image"
                       src="https://www.paypalobjects.com/<?php echo localize("SYSTEM_LOCALE"); ?>/i/btn/btn_donate_SM.gif"
                       border="0"
                       name="submit" alt="PayPal - The safer, easier way to pay online!">
                <img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1"
                     height="1">
                </form>
            </div>
        </div>
        <br><br>


        <div id="about-github" style="background-color: #d7d7d7; padding: 20px; text-align: center;">
            <div>
                <?php echo localize("MODAL_ABOUT_GITHUB"); ?><br>
                <div style="text-align: center; font-size: 50px;">
                    <i class="fab fa-github-alt"></i>
                </div>
            </div>
            <div class="row" style="justify-content: center; align-items: center; text-align: center;">
                <button id="btnFA" class="btn btn-primary"
                        onclick="window.open('https://github.com/crieke/RoonServer_Asustor_Installer')">
                    <i class="fab fa-github"></i>
                    <?php echo localize("MODAL_ABOUT_BTN_GITHUB"); ?><br>
                </button>
            </div>
        </div>

        <br><br>

        <h4><?php echo localize("MODAL_ABOUT_THANKS"); ?>:</h4><br>
        <div id="contrib"></div>

        <script>
            var manualContrib = JSON.parse('<?php  echo json_encode($ContributorsManual)?>');
            $(document).ready(function () {
                $('#contrib').html('<div class="fa-4x text-center" style="text-align: center;"><svg id="loading" width="70" height="70"></svg></div>');

                var repo = 'https://api.github.com/repos/crieke/RoonServer_Asustor_Installer/contributors';


                requestJSON(repo, function (json) {

                    var gitAndManual = json.concat(manualContrib);
                    var outhtml = '<ul class=\"media-list\">';


                    jQuery.each(gitAndManual, function (i, obj) {
                        // else we have a user and we display their info
                        var username = obj.login;
                        var aviurl = obj.avatar_url;
                        var userprofile = obj.html_url;
                        var contr = obj.contributions;
                        var descrText = obj.description;

                        if (aviurl == undefined) {
                            aviurl = 'assets/identicon.php?string=' + username;
                        }

                        outhtml = outhtml + '<li id="contriblist" class="media contriblist">';
                        outhtml = outhtml + '<span class="pull-left">';
                        if (userprofile != undefined) {
                            outhtml = outhtml + '<a href="' + userprofile + '" target="_blank">';
                        }
                        outhtml = outhtml + '<img class="media-object d-block" style="max-height: 80px" src="' + aviurl + '" alt="...">';
                        if (userprofile != undefined) {
                            outhtml = outhtml + '</a>';
                        }
                        outhtml = outhtml + '</span>';
                        outhtml = outhtml + '<div class="media-body" style="padding-left: 15px; display:inline;">';
                        if (userprofile != undefined) {
                            outhtml = outhtml + '<a href="' + userprofile + '" target="_blank">';
                        }
                        outhtml = outhtml + '<h4 class="media-heading">' + username + '</h4>';
                        if (userprofile != undefined) {
                            outhtml = outhtml + '</a>';
                        }
                        if (contr != undefined) {
                            outhtml = outhtml + '<div  style="ghicon"><i class="fab fa-github"></i>-Contributions: ' + contr + '</div>';
                        }
                        if (descrText != undefined) {
                            outhtml = outhtml + '<p> ' + descrText + '</p>';
                        }
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

            var s = Snap("#loading");

            var svgSize = 70;
            var RoonCircle = s.circle(svgSize / 2, svgSize / 2, svgSize / 2);

            var maskRect = s.paper.rect(0, 0, svgSize / 2, svgSize);
            maskRect.attr({
                fill: "#fff"
            });
            RoonCircle.attr({
                mask: maskRect
            });

            linespacing = svgSize / 100 * 5;
            centerpoint = svgSize / 2;
            linew = svgSize / 100 * 5;
            line1h = svgSize / 100 * 90;
            line2h = svgSize / 100 * 40;
            line3h = svgSize / 100 * 60;
            line4h = svgSize / 100 * 22;
            var line1 = s.rect(centerpoint + (0 * (linew + linespacing)) + linespacing, centerpoint - (line1h / 2), linew, line1h);
            var line2 = s.rect(centerpoint + (1 * (linew + linespacing)) + linespacing, centerpoint - (line2h / 2), linew, line2h);
            var line3 = s.rect(centerpoint + (2 * (linew + linespacing)) + linespacing, centerpoint - (line3h / 2), linew, line3h);
            var line4 = s.rect(centerpoint + (3 * (linew + linespacing)) + linespacing, centerpoint - (line4h / 2), linew, line4h);

            function cw($c_height) {

                var $arr = new Array();
                $arr['y'] = (centerpoint - ($c_height / 2) / 100 * svgSize);
                $arr['height'] = $c_height / 100 * svgSize;
                return $arr;

            }

            function roonAnimate() {
                line1.animate(
                    cw(52), 200, function () {
                        this.animate(
                            cw(96), 200, function () {
                                this.animate(
                                    cw(80), 240, function () {
                                        this.animate(
                                            cw(86), 200, function () {
                                                this.animate(
                                                    cw(92), 40, function () {
                                                        this.animate(
                                                            cw(86), 40, function () {
                                                                this.animate(
                                                                    {
                                                                        height: svgSize / 100 * 90,
                                                                        y: svgSize / 100 * 5
                                                                    }, 40
                                                                )
                                                            }
                                                        )
                                                    }
                                                )
                                            }
                                        )
                                    }
                                )
                            }
                        )
                    }
                );

                line2.animate(
                    cw(76), 280, function () {
                        this.animate(
                            cw(30), 200, function () {
                                this.animate(
                                    cw(50), 240, function () {
                                        this.animate(
                                            cw(55), 120, function () {
                                                this.animate(
                                                    cw(57), 40, function () {
                                                        this.animate(
                                                            cw(40), 40, function () {
                                                                this.animate(
                                                                    {
                                                                        height: svgSize / 100 * 60,
                                                                        y: svgSize / 100 * 20
                                                                    }, 40
                                                                )
                                                            }
                                                        )
                                                    })
                                            })
                                    })
                            })
                    });

                line3.animate(
                    cw(63), 120, function () { // 3 frames
                        this.animate(
                            cw(30), 240, function () { // 6 frames
                                this.animate(
                                    cw(70), 200, function () { //5 frames
                                        this.animate(
                                            cw(54), 240, function () { // 6 frames
                                                this.animate(
                                                    cw(56), 40, function () { //1 frame
                                                        this.animate(
                                                            cw(42), 40, function () { // 1 frame
                                                                this.animate(
                                                                    cw(58), 40), function () {
                                                                    this.animate(
                                                                        {
                                                                            height: svgSize / 100 * 22,
                                                                            y: svgSize / 100 * 56
                                                                        }, 40
                                                                    )
                                                                }
                                                            })
                                                    })
                                            })
                                    })
                            })
                    });

                line4.animate(
                    cw(14), 200, function () {
                        this.animate(
                            cw(26), 200, function () {
                                this.animate(
                                    cw(8), 240, function () {
                                        this.animate(
                                            cw(20), 200, function () {
                                                this.animate(
                                                    cw(62), 40, function () {
                                                        this.animate(
                                                            cw(24), 40, function () {
                                                                this.animate(
                                                                    {
                                                                        height: svgSize / 100 * 40,
                                                                        y: svgSize / 100 * 30
                                                                    }, 40
                                                                )
                                                            }
                                                        )
                                                    }
                                                )
                                            }
                                        )
                                    }
                                )
                            }
                        )
                    }
                );


            }

            roonAnimate();
            setInterval(roonAnimate, 1000);
        </script>

        <br>
        <br>
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
                    'assetName' => 'Snap.svg',
                    'author' => 'Adobe',
                    'href' => 'https://github.com/adobe-webplatform/Snap.svg',
                    'license' => 'Apache 2.0'
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
        <button type="button" class="btn btn-outline-secondary"
                data-dismiss="modal"><?php echo localize("BTN_CLOSE"); ?></button>
