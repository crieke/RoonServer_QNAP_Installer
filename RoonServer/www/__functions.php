<?php

if (!isset($_COOKIE['as_sid']) && ($_COOKIE['as_logout'] == "1") || empty($_COOKIE['as_sid'])) {
    die("not logged in! ;)");
}

function write_ini($file, $array, $i = 0){
    $str="";
    foreach ($array as $k => $v){
      if (is_array($v)){
        $str.=str_repeat(" ",$i*2)."[$k]".PHP_EOL; 
        $str.=put_ini_file("",$v, $i+1);
      }else
        $str.=str_repeat(" ",$i*2)."$k = $v".PHP_EOL; 
    }
  if($file)
      return file_put_contents($file,$str);
    else
      return $str;
  }
function debugToConsole($msg) { 
        echo "<script>console.log(".json_encode($msg).")</script>";
}

function get_web_page($url)
{
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING => "",       // handle all encodings
        CURLOPT_USERAGENT => "spider", // who am i
        CURLOPT_AUTOREFERER => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT => 120,      // timeout on response
        CURLOPT_MAXREDIRS => 10,       // stop after 10 redirects
        CURLOPT_SSL_VERIFYPEER => false,    // Disabled SSL Cert checks
        CURLOPT_SSL_VERIFYHOST => false
    );

    $ch = curl_init($url);
    curl_setopt_array($ch, $options);
    $content = curl_exec($ch);
    $err = curl_errno($ch);
    $errmsg = curl_error($ch);
    $header = curl_getinfo($ch);
    curl_close($ch);

    $header['errno'] = $err;
    $header['errmsg'] = $errmsg;
    $header['content'] = $content;
    return $header;
    unset($options);
}

function getFoldersAt($dir)
{
    header('Content-Type: application/json');

    $list = array(); //main array

    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) != false) {

                if ($file == "." or $file == "..") {
                    //...
                } else { //create object
                    $faCssClass = 'far fa-folder';
                    $browseable = true;
                    $selectedDir = $dir . '/' . $file;

                    switch ($file) {
                        case "RoonServer":
                            if (file_exists($dir.'/'.$file . '/Database/Registry') && is_dir($dir.'/'.$file . '/Database/Registry')) {
                                $faCssClass = 'fas fa-file-audio';
                                $selectedDir = $dir;
                                $browseable = false;
                            }
                            break;
                            case "RAATServer":
                            if (file_exists($dir.'/'.$file . '/Logs') && is_dir($dir.'/'.$file . '/Logs')) {
                                $faCssClass = 'fas fa-file-audio';
                                $selectedDir = $dir;
                                $browseable = false;
                            }
                            break;
                            case "RoonGoer":
                            if (file_exists($dir.'/'.$file .  '/Database/Registry') && is_dir($dir.'/'.$file .  '/Database/Registry')) {
                                $faCssClass = 'fas fa-file-audio';
                                $selectedDir = $dir;
                                $browseable = false;
                            }
                            break;
                            
                            default:
                            $subDirs = glob($selectedDir . '/*' , GLOB_ONLYDIR);
                            if ( empty($subDirs) ) {
                                $browseable = false;
                            }
                            break;
                    }

                    if (is_dir($dir.'/'.$file)){ 
                        $list3 = array(
                            'text' => $file,
                            'path' => $selectedDir,
                            'iconCls' => 'external',
                            'anychildren' => $browseable,
                            'faCssClass' => $faCssClass);
                        array_push($list, $list3);    
                    }
                }
            }
        }

        //$return_array = array('content' => $list);
        $return_array = $list;

        echo json_encode($return_array);
    }
}


function getTreeRoot($strSessionID)
{
    $strUrl = NASLOCALHOST . '/portal/apis/fileExplorer/fileExplorer.cgi?sid=' . $strSessionID . '&showrecyclebin=false&showhome=false&act=file_list&onlydir=false';
    $arrData = get_web_page($strUrl);
    $arrShareTree = json_decode($arrData['content'], 1);
    $arrShareTreeData = array();
    foreach ($arrShareTree['data'] as $arrTemp) {

            // Set folder icon and check for external devices
            switch ($arrTemp['share_folder_type']) {
                case "external_share":
                    $arrTemp['iconCls'] = 'fas fa-hdd';
                    break;
                default:
                    $arrTemp['iconCls'] = 'far fa-folder';
                    break;
            }
        switch ($arrTemp['exist_subdir']) {
            case "1":
                $arrTemp['subdir'] = 'true';
                break;
            default:
                $arrTemp['subdir'] = 'false';
                break;
        }


        $arrShareTreeData[] = array(
                'text' => $arrTemp['filename'],
                'path' => $arrTemp['file_path'],
                'faCssClass' => $arrTemp['iconCls'],
                'anychildren' => $arrTemp['subdir']
            );
    }
    return ($arrShareTreeData);
}


function removeFirstChildDir($path)
{
    $path = explode('/', $path);
    unset($path[1]);
    $db_path = implode('/', $path);
    return $db_path;
}

function set_db_path($folder)
{
    $config = array('DB_Path' => $folder);
    write_ini("/usr/local/AppCentral/RoonServer/etc/RoonServer.conf", $config);
}

function getTreeAt($folder, $strSessionID)
{
    $folder = str_replace('+', '%20', filter_var($folder, FILTER_SANITIZE_STRING));
    if (strlen($folder) > 3) {
        $strUrl = NASLOCALHOST . '/portal/apis/fileExplorer/fileExplorer.cgi?sid='. $strSessionID .'&act=file_list&path=' . $folder . '&onlydir=true';
        $arrData = get_web_page($strUrl);
        $arrShareTree = json_decode($arrData['content'], 1);

        $arrShareTreeData = array();



        foreach ($arrShareTree['data'] as $arrTemp) {

            if (
                substr($arrTemp['filename'], 0, 1) != "@" &&
                substr($arrTemp['filename'], 0, 1) != "." &&
                substr($arrTemp['filename'], 0, 2) != ".."
            ) {

                $arrTemp['iconCls'] = 'far fa-folder';
                $browseable = true;
                switch ($arrTemp['filename']) {
                    case "RoonServer":
                        if (file_exists('/share' . mb_convert_encoding($arrTemp['file_path'], "UTF-8", 'auto') . '/Logs') && is_dir('/share' . mb_convert_encoding($arrTemp['file_path'], "UTF-8", 'auto') . '/Logs')) {
                            $arrTemp['iconCls'] = 'fas fa-file-audio';
                            $arrTemp['id'] = dirname($arrTemp['id']);
                            $browseable = false;
                        }
                        break;
                    case "RAATServer":
                        if (file_exists('/share' . mb_convert_encoding($arrTemp['file_path'], "UTF-8", 'auto') . '/Logs') && is_dir('/share' . mb_convert_encoding($arrTemp['file_path'], "UTF-8", 'auto') . '/Logs')) {
                            $arrTemp['iconCls'] = 'fas fa-file-audio';
                            $arrTemp['id'] = dirname($arrTemp['id']);
                            $browseable = false;
                        }
                        break;
                    case "RoonGoer":
                        if (file_exists('/share' . mb_convert_encoding($arrTemp['file_path'], "UTF-8", 'auto') . '/Database/Registry') && is_dir('/share' . mb_convert_encoding($arrTemp['file_path'], "UTF-8", 'auto') . '/Database/Registry')) {
                            $arrTemp['iconCls'] = 'fas fa-file-audio';
                            $arrTemp['id'] = dirname($arrTemp['id']);
                            $browseable = false;
                        }
                        break;
                }

                switch ($arrTemp['exist_subdir']) {
                    case "1":
                        $arrTemp['subdir'] = 'true';
                        break;
                    default:
                        $arrTemp['subdir'] = 'false';
                        break;
                }
                $arrShareTreeData[] = array(
                    'text' => $arrTemp['filename'],
                    'path' => $arrTemp['file_path'],
                    'faCssClass' => $arrTemp['iconCls'],
                    'anychildren' => $browseable
                );
            }
        }
        return ($arrShareTreeData);
    }
}



unset($arrData);
unset($arrTemp);
unset($arrNodes);

function localize($phrase)
{
    /* Static keyword is used to ensure the file is loaded only once */
    static $translations = NULL;
    $default_file = APPINSTALLPATH . '/www/i18n/locale-eng.json';
    if (is_null($translations)) {
        $lang_file = APPINSTALLPATH . '/www/i18n/locale-' . NAS_LANG . '.json';
        /* If no instance of $translations has occured load the language file */
        if (!file_exists($lang_file)) {
            $lang_file = $default_file;
        }
        $lang_file_content = file_get_contents($lang_file);
        /* Load the language file as a JSON object
           and transform it into an associative array */
        $translations = json_decode($lang_file_content, true);
    }
    // Use english as a fallback option if string does not exist in translated file
    if (!isset($translations[$phrase])) {
        $default_file_content = file_get_contents($default_file);
        $default_translations = json_decode($default_file_content, true);

        if (!isset($default_translations[$phrase])) {
            // Return $phrase if also fallback option fails.
            return $phrase;
        } else {
            // Return fallback language
            return $default_translations[$phrase];
        }
    } else {
        // Return Translation
        return $translations[$phrase];
    }
}

function isRunning($pidfile, $option = null)
{
    if (file_exists($pidfile)) {
        $pidfilecontent = file($pidfile, FILE_IGNORE_NEW_LINES);
        if (is_dir('/proc/' . $pidfilecontent[0])) {
            $pid = $pidfilecontent[0];
            $running = true;
        } else {
            $pid = "";
            $running = false;
        }
    } else {
        $pid = "";
        $running = false;
    }
    switch ($option) {
        case 'getpid':
            return $pid;
            break;
        default:
            return $running;
    }
}

function GetAsoundCards()
{
    $cards = array();
    $cardsPath = "/proc/asound/cards";
    $cardsContents = trim(file_get_contents($cardsPath));
    $splitNewline = explode(PHP_EOL, $cardsContents);
    foreach ($splitNewline as $line => $key) {
        if (strpos($key, "]: ")) {
            $r = parseCardsLine(trim($key));
            $cards[] = $r;
        }
    }
    return $cards;
}
function parseCardsLine($str)
{
    $result = array();
    $r = explode(" - ", $str);
    $result["connection"] = substr($r[0], strpos($r[0], ']: ') +3 );
    preg_match('/\[([\s\S])\w+/', $r[0], $matches, PREG_OFFSET_CAPTURE);
    $result["name"] = $r[1];
    return $result;
}
function acardsNice()
{
    $arrSCards = GetAsoundCards();
    $strOutput = "";
    foreach ($arrSCards as &$value) {
        $strSCardName = $value['name'];
        $strSCardConnection = $value['connection'];
        $strOutput = $strOutput . '<li class="list-group-item justify-content-between"><b>' . $strSCardName . '</b><br>' . $strSCardConnection . '</li>';
    }
    return $strOutput;
}
function downloadLogs($strSessionID, $dblocation)
{
}

function displayStorage($diskspace){
$bytes = $diskspace;
$si_prefix = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
$base = 1024;
$class = min((int)log($bytes , $base) , count($si_prefix) - 1);
return sprintf('%1.2f' , $bytes / pow($base,$class)) . ' ' . $si_prefix[$class];
}

?>