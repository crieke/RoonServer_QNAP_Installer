<!DOCTYPE html>
<html>
    <?php
    $WEBAPP_ROOT = explode('/', $_SERVER['REQUEST_URI']);
    $WEBAPP_ROOT = "/".$WEBAPP_ROOT[1] . "/qpkg/RoonServer";        
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
    
    <div class='section' style='padding: 0; margin-top: 50px;'>
        <img class='gradient' src='img/section_gradient_top.png'/>
        <img src='img/roon_logo.png'/>
    </div>

    <div class='section'>
        <style>
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
        <h1>Live Log</h1>
        <a class='commonbtn button' href='downloadlog.php'>Download all Logs</a>
        <br><br>
        <h2>QNAP Command-Line Log</h2>
        <div id="feed-BashLog"></div>
<script type="text/javascript">
var refreshtime=10;
function readBashLog()
{
asyncAjax1("GET","logfile_Bash.php",Math.random(),display1,{});
setTimeout(readBashLog,refreshtime);
}
function display1(xhr1,cdat1)
{
 if(xhr1.readyState==4 && xhr1.status==200)
 {
   document.getElementById("feed-BashLog").innerHTML=xhr1.responseText;
 }
}
function asyncAjax1(method1,url1,qs1,callback1,callbackData1)
{
    var xmlhttp1=new XMLHttpRequest();
    //xmlhttp1.cdat1=callbackData1;
    if(method1=="GET")
    {
        url1+="?"+qs1;
    }
    var cb1=callback1;
    callback1=function()
    {
        var xhr1=xmlhttp1;
        //xhr1.cdat1=callbackData1;
        var cdat1b=callbackData1;
        cb1(xhr1,cdat1b);
        return;
    }
    xmlhttp1.open(method1,url1,true);
    xmlhttp1.onreadystatechange=callback1;
    if(method1=="POST"){
            xmlhttp1.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xmlhttp1.send(qs1);
    }
    else
    {
            xmlhttp1.send(null);
    }
}
readBashLog();
</script>
        
        
         <h2>Roon Server Log</h2>
        <div id="feed-RoonLog"></div>
<script type="text/javascript">
var refreshtime=10;
function readRoonLog()
{
asyncAjax("GET","logfile_Roon.php",Math.random(),display,{});
setTimeout(readRoonLog,refreshtime);
}
function display(xhr,cdat)
{
 if(xhr.readyState==4 && xhr.status==200)
 {
   document.getElementById("feed-RoonLog").innerHTML=xhr.responseText;
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
readRoonLog();
</script>
        
  
<br>
<?php include 'footer.php';?>
<script>
    showmain();
</script>

</body>
</html>

