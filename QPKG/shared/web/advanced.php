<!DOCTYPE html>
<html>
    <?php
    $WEBAPP_ROOT = explode('/', $_SERVER['REQUEST_URI']);
    $WEBAPP_ROOT = "/".$WEBAPP_ROOT[1] . "/qpkg/RoonServer";
    $QPKGconfig = parse_ini_file("/etc/config/qpkg.conf", true, INI_SCANNER_RAW);
    $ROON_PATH = $QPKGconfig['RoonServer']['Install_Path'];
    $SMBconfig = parse_ini_file("/etc/config/smb.conf", true, INI_SCANNER_RAW);
    $RoonServer_sharedfolder = $SMBconfig['RoonServer']['path'];
    $directory_database = "$RoonServer_sharedfolder" . "/RoonServer";
    $dbsize = round(dirSize($directory_database) / 1024 / 1024 ) . " MB";
    $df = round(disk_free_space($directory_database) / 1024 / 1024 / 1024);

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
        <script src="<?php echo $WEBAPP_ROOT; ?>/js/jquery-latest.min.js"></script>
        <script src="<?php echo $WEBAPP_ROOT; ?>/js/bootstrap.min.js"></script>


        <style>
            body {
                color: #333;
                font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
                font-size: 14px;
                line-height: 1.42857;
            }

            .black_overlay {
                display: none;
                position: absolute;
                top: 0%;
                left: 0%;
                width: 100%;
                height: 100%;
                background-color: black;
                z-index: 1001;
                -moz-opacity: 0.8;
                opacity: .80;
                filter: alpha(opacity=80);
            }
            .white_content {
                display: none;
                z-index: 1002;
                overflow: auto;
            }

            .row {
                margin-left: -15px;
                margin-right: -15px;
                text-align: left;
                margin: 0 auto;
                max-width: 1200px;
            }

            .btn-group-vertical > .btn-group::after, .btn-group-vertical > .btn-group::before, .btn-toolbar::after, .btn-toolbar::before, .clearfix::after, .clearfix::before, .container-fluid::after, .container-fluid::before, .container::after, .container::before, .dl-horizontal dd::after, .dl-horizontal dd::before, .form-horizontal .form-group::after, .form-horizontal .form-group::before, .modal-footer::after, .modal-footer::before, .modal-header::after, .modal-header::before, .nav::after, .nav::before, .navbar-collapse::after, .navbar-collapse::before, .navbar-header::after, .navbar-header::before, .navbar::after, .navbar::before, .pager::after, .pager::before, .panel-body::after, .panel-body::before, .row::after, .row::before {
                content: " ";
                display: table;
            }

            .col-sm-6 {
                width: 50%;
            }
            .col-sm-1, .col-sm-10, .col-sm-11, .col-sm-12, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9 {
                float: left;
            }
            .col-lg-1, .col-lg-10, .col-lg-11, .col-lg-12, .col-lg-2, .col-lg-3, .col-lg-4, .col-lg-5, .col-lg-6, .col-lg-7, .col-lg-8, .col-lg-9, .col-md-1, .col-md-10, .col-md-11, .col-md-12, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9, .col-sm-1, .col-sm-10, .col-sm-11, .col-sm-12, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9, .col-xs-1, .col-xs-10, .col-xs-11, .col-xs-12, .col-xs-2, .col-xs-3, .col-xs-4, .col-xs-5, .col-xs-6, .col-xs-7, .col-xs-8, .col-xs-9 {
                min-height: 1px;
                padding-left: 15px;
                padding-right: 15px;
                position: relative;
            }

            .statusheader {
                font-size: 1.2em;
                font-weight: 700;
            }

            }
            .text-success {
                color: #3c763d;
            }

            #feed-BashLog,
            #feed-RoonLog{
                overflow: auto;
                overflow-y: scroll;
                min-height: 55px;
                height: 250px;
                min-height: 55px;
                width: 800px;
                margin: auto;
                padding: 5px 0 0 0;
                font-family: "Input Mono", monospace;
                text-align: left;
                font-weight: 400;
                font-size: 0.8em;
                line-height: 1.4em;
                color: rgba(0,0,0,.61);
                border-radius: 2px;
                box-shadow: inset 0 0 2px rgba(0,0,0,.1);
                background-color: #EBEBEB;
            }
        </style>

    </head>

    <body class="pull_top">
        <?php
        function liveExecuteCommand($cmd)
        {

            while (@ ob_end_flush()); // end all output buffers if any

            $proc = popen("$cmd 2>&1 ; echo Exit status : $?", 'r');

            $live_output     = "";
            $complete_output = "";

            while (!feof($proc))
            {
                $live_output     = fread($proc, 4096);
                $complete_output = $complete_output . $live_output;
                echo "$live_output";
                @ flush();
            }

            pclose($proc);

            // get exit status
            preg_match('/[0-9]+$/', $complete_output, $matches);

            // return exit status and intended output
            return array (
                'exit_status'  => intval($matches[0]),
                'output'       => str_replace("Exit status : " . $matches[0], '', $complete_output)
            );
        }
        ?>
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

            <div class='section' style='padding: 0; margin-top: 50px;'>
                <img class='gradient' src='img/section_gradient_top.png'/>
                <img src='img/roon_logo.png'/>
            </div>
            <div class='section'>
                <h1>Advanced</h1>

                <div class="row" style="width:750px;">
                    <p style="width:600px; font-size:18px; text-align:center;margin: 0 auto;">If you ever have issues with RoonServer, you can reinstall the latest version here or start fresh by resetting your database.<br><br> Your Roon database includes playlists, edits, play history, configuration details, preferences and more. When performing a reset, these information will be wiped. Your media files will not be touched, but they need to be re-added to your new database.<br><br></p>
                    <div class='col-sm-6' style='margin-top: 20px; min-height: 100px;'>
                        <div class='statusheader'>Roon Database &amp; Settings</div>
                        <div style='width: 290px;'>
                            <button class="btn btn-primary action_delete" style='min-width: 110px; float: right; line-height: 15px;' href="javascript:void(0)" onclick="document.getElementById('confirmboxdeletedb').style.display='block';document.getElementById('fade').style.display='block'">Reset</button>
                            <div class="data_status text-success" style="font-size: 1.5em;">OK</div>
                        </div>
                        <div style='font-size: 1.0em;' class='data_description'>
                            <?php

                            function dirSize($directory) {
                                $size = 0;
                                foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file){
                                    $size+=$file->getSize();
                                }
                                return $size;
                            } 
                            echo 'Database Size: ' . $dbsize . " -  Free space: $df GB";

                            ?>


                        </div>
                    </div>
                    <div class='col-sm-6' style='margin-top: 20px; min-height: 100px;'>
                        <div class='statusheader'>Roon Server Software</div>
                        <div style='width: 290px;'>
                            <button id="prereinstallbtn" class="btn btn-primary action_delete" style='min-width: 110px; float: right; line-height: 15px;' href="javascript:void(0)" onclick="document.getElementById('confirmboxreinstall').style.display='block';document.getElementById('fade').style.display='block';" type="submit" name="reinstall" >Reinstall</button>
                            <div class="data_status text-success" style="font-size: 1.5em;">OK</div>
                        </div>
                        <div style='font-size: 1.0em;' class='data_description'>
                            <?php
                            $lines = file(rtrim("$ROON_PATH") . "/RoonServer/VERSION");
                            echo "Version " . $lines[1];
                            ?>
                        </div>
                    </div> 
                </div>

                <div id="confirmboxreinstall" class="white_content" >
                    <div id="confirm_modal" class="modal in" tabindex="-1" style="display: block;">
                        <form>
                            <div id="popUp" class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title title">Reinstall RoonServer?</h4>
                                    </div>
                                    <div class="modal-body">
                                        <div class="text">	
                                            <p>Are you sure you want to reinstall RoonServer? The latest version will be downloaded and put into place. RoonServer will be stopped during this procedure.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <div style='width: 290px;'>
                                                <button id="reinstallbtn" class="btn btn-default ok" onclick="document.getElementById('confirmboxreinstall').style.display='none';document.getElementById('reinstall').style.display='block';document.getElementById('dialogtitle').innerHTML = 'Reinstalling RoonServer...';">Yes</button>
                                                <button class="btn btn-default cancel" type="button" data-dismiss="modal" onclick="document.getElementById('confirmboxreinstall').style.display='none';document.getElementById('fade').style.display='none'">No</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div id="confirmboxdeletedb" class="white_content" >
                    <div id="confirm_modal" class="modal in" tabindex="-1" style="display: block;">
                        <form>
                            <div id="popUp" class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title title">Reset Database?</h4>
                                    </div>
                                    <div class="modal-body">
                                        <div class="text">	
                                            <p>Are you sure you want to reset all settings and databases?</p><p>This action is destructive and can not be undone.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <div style='width: 290px;'>
                                                <button id="removedb" class="btn btn-default ok" onclick="document.getElementById('confirmboxdeletedb').style.display='none';document.getElementById('reinstall').style.display='block';document.getElementById('dialogtitle').innerHTML = 'Resetting database...';">Yes</button>
                                                <button class="btn btn-default cancel" type="button" data-dismiss="modal" onclick="document.getElementById('confirmboxdeletedb').style.display='none';document.getElementById('fade').style.display='none'">No</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div id="reinstall" class="white_content" >
                    <div id="confirm_modal" class="modal in" tabindex="-1" style="display: block;">
                        <form>
                            <div id="popUp" class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 id="dialogtitle" class="modal-title title">Reinstalling RoonServer...</h4>
                                    </div>
                                    <div class="modal-body">
                                        <div id="feed-BashLog" style="width:530px; height:250px;">
                                            <script>
                                                $('#reinstallbtn').click(function(){
                                                    $("#feed-BashLog").load('<?php echo $WEBAPP_ROOT; ?>/redownloadbinaries.php');
                                                    return false;
                                                });
                                                $('#removedb').click(function(){
                                                    $("#feed-BashLog").load('<?php echo $WEBAPP_ROOT; ?>/removedb.php');
                                                    return false;
                                                });
                                            </script>
                                        </div>
                                        <div class="modal-footer">
                                            <div style='width: 290px;'>
                                                <button class="btn btn-default ok" type="submit">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div id="fade" class="black_overlay"></div>


            </div>

            <?php include 'footer.php';?>

            <script>
                function showmain() {
                    $(".allbodyloading").hide();
                    $(".allbody").show();
                }
                showmain();

                var refreshtime=10;
                function tc()
                {
                    asyncAjax("GET","reinstall_readbash.php",Math.random(),display,{});
                    setTimeout(tc,refreshtime);
                }
                function display(xhr,cdat)
                {
                    if(xhr.readyState==4 && xhr.status==200)
                    {
                        document.getElementById("feed-BashLog").innerHTML=xhr.responseText;
                    }
                }
                function asyncAjax(method,url,qs,callback,callbackData)
                {
                    var xmlhttp=new XMLHttpRequest();
                    //xmlhttp.cdat=callbackData;
                    if(method=="GET")
                    {
                        url+="?"+qs;
                    }
                    var cb=callback;
                    callback=function()
                    {
                        var xhr=xmlhttp;
                        //xhr.cdat=callbackData;
                        var cdat2=callbackData;
                        cb(xhr,cdat2);
                        return;
                    }
                    xmlhttp.open(method,url,true);
                    xmlhttp.onreadystatechange=callback;
                    if(method=="POST"){
                        xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
                        xmlhttp.send(qs);
                    }
                    else
                    {
                        xmlhttp.send(null);
                    }
                }
                tc();
            </script>
        </div>
    </body>
</html>

