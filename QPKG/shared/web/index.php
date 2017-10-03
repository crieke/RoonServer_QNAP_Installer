<!DOCTYPE html>
<html>
    <?php
    $WEBAPP_ROOT = explode('/', $_SERVER['REQUEST_URI']);
    $WEBAPP_ROOT = "/".$WEBAPP_ROOT[1] . "/qpkg/RoonServer";
    $QPKGconfig = parse_ini_file("/etc/config/qpkg.conf", true, INI_SCANNER_RAW);
    ?>
    <head>
        <title>Roon Server</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <!-- Styles -->
        <link href="<?php echo $WEBAPP_ROOT; ?>/css/bootstrap/bootstrap.min.css" rel="stylesheet" />
        <link rel="stylesheet" type="text/css" href="<?php echo $WEBAPP_ROOT; ?>/css/roon.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $WEBAPP_ROOT; ?>/css/cube-grid.css" />
        <link href='//fonts.googleapis.com/css?family=Lato:300,400,700,900,300italic,400italic,700italic,900italic' rel='stylesheet' type='text/css' />

    </head>

    <body class="pull_top">
        <script src="<?php echo $WEBAPP_ROOT; ?>/js/jquery-latest.min.js"></script>
        <script src="<?php echo $WEBAPP_ROOT; ?>/js/bootstrap.min.js"></script>

        <script>
        function showmain() {
            $(".allbodyloading").hide();
            $(".allbody").show();
        }
        </script>


        <div class='allbodyloading'>
            <div class="sk-cube-grid">
            <div class="sk-cube sk-cube1"></div>
            <div class="sk-cube sk-cube2"></div>
            <div class="sk-cube sk-cube3"></div>
            <div class="sk-cube sk-cube4"></div>
            <div class="sk-cube sk-cube5"></div>
            <div class="sk-cube sk-cube6"></div>
            <div class="sk-cube sk-cube7"></div>
            <div class="sk-cube sk-cube8"></div>
            <div class="sk-cube sk-cube9"></div>
            </div>
        </div>

        <div class='allbody' style='display:none;'>

        <?php include 'topbar.php';?>

            <?php
                $ROON_PATH = $QPKGconfig['RoonServer']['Install_Path'];
?>
            <div class='section' style='padding: 0; margin-top: 50px;'>
                <img class='gradient' src='img/section_gradient_top.png'/>
                <img src='img/roon_logo.png'/>
            </div>

            <div class='section'>
                <h1>RoonServer for QNAP (x64)</h1>
                <p style='max-width: 600px;'>
                <b>Installed RoonServer:</b> <?php
                $lines = file(rtrim("$ROON_PATH") . "/RoonServer/VERSION");
                echo $lines[1]; //line 2
                ?>
                <br>
                <b>QPKG-Version:</b> <?php
                $QPKG_VERSION = $QPKGconfig['RoonServer']['Version'];
                echo $QPKG_VERSION;
                ?>
                <br><br>
                Control Roon Server with a PC running Roon or a mobile device running Roon Remote. For macOS and Windows, open the "More" tab to download from the Roon Labs download page. For iOS and Android devices, get the Roon Remote app in the App Store or Play Store.</p>
        <br>
        <div class="allinonedownloads margin-center">
            <a class="commonbtn" href="https://play.google.com/store/apps/details?id=com.roon.mobile" target="_blank">
                <img src="img/download-google-play.png">
            </a>
            <a class="commonbtn" href="https://itunes.apple.com/WebObjects/MZStore.woa/wa/viewSoftware?id=1014764083&mt=8" target="_blank">
                <img src="img/download-app-store.png">
            </a>
        </div>
<?php include 'footer.php';?>
        <script>
            showmain();
        </script>
    </body>
</html>

