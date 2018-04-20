<?php
if (!isset($_COOKIE['NAS_USER']) || empty($_COOKIE['NAS_USER'])) {
    die("not logged in! ;)");
}

$qpkg_conf = parse_ini_file('/etc/config/qpkg.conf', 1, INI_SCANNER_RAW);
$roon_qpkg_conf = $qpkg_conf['RoonServer'];
$qpkgpath = $roon_qpkg_conf['Install_Path'];


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
}

function getTreeRoot($strSessionID)
{
    $strUrl = QNAPDOCROOT . '/filemanager/utilRequest.cgi?func=get_tree&sid=' . $strSessionID . '&is_iso=0&node=share_root';
    $arrData = get_web_page($strUrl);
    $arrShareTree = json_decode($arrData['content'], 1);
    $arrShareTreeData = array();


    //echo "<pre>";
    foreach ($arrShareTree as $arrTemp) {
        if (
            substr($arrTemp[''], 0, 1) !== "@" &&
            substr($arrTemp[''], 0, 1) !== "." &&
            substr($arrTemp[''], 0, 1) !== ".."
        ) {
            // Set folder icon and check for external devices
            switch ($arrTemp['iconCls']) {
                case "external":
                    $arrTemp['iconCls'] = 'fas fa-hdd';
                    break;
                default:
                    $arrTemp['iconCls'] = 'far fa-folder';
                    break;
            }

            $arrShareTreeData[] = array(
                'text' => $arrTemp['text'],
                'path' => $arrTemp['id'],
                'faCssClass' => $arrTemp['iconCls'],
                'anychildren' => true
            );


            //$arrNodes = getTreeAt(utf8_encode($arrTemp['text']),$strSessionID );


        }
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
    shell_exec('setcfg RoonServer DB_Path "/share' . $folder . '" -f /etc/config/qpkg.conf');
    shell_exec(QPKGINSTALLPATH . '/Roonserver.sh start');
}

function getTreeAt($folder, $strSessionID)
{
    $folder = str_replace('+', '%20', filter_var($folder, FILTER_SANITIZE_STRING));
    if (strlen($folder) > 3) {
        $strUrl = QNAPDOCROOT . '/filemanager/utilRequest.cgi?func=get_tree&sid=' . $strSessionID . '&is_iso=0&node=' . $folder;
        $arrData = get_web_page($strUrl);
        $arrShareTree = json_decode($arrData['content'], 1);


        $arrShareTreeData = array();
        //echo "<pre>";

        foreach ($arrShareTree as $arrTemp) {
            if (
                substr($arrTemp['text'], 0, 1) != "@" &&
                substr($arrTemp['text'], 0, 1) != "." &&
                substr($arrTemp['text'], 0, 1) != ".."
            ) {

                $arrTemp['iconCls'] = 'far fa-folder';
                $browseable = true;

                switch ($arrTemp['text']) {
                    case "RoonServer":
                        if (file_exists('/share' . $arrTemp['id'] . '/Logs') && is_dir('/share' . $arrTemp['id'] . '/Logs')) {
                            $arrTemp['iconCls'] = 'fas fa-file-audio';
                            $arrTemp['id'] = dirname($arrTemp['id']);
                            $browseable = false;

                        }
                        break;
                    case "RAATServer":
                        if (file_exists('/share' . $arrTemp['id'] . '/Logs') && is_dir('/share' . $arrTemp['id'] . '/Logs')) {
                            $arrTemp['iconCls'] = 'fas fa-file-audio';
                            $arrTemp['id'] = dirname($arrTemp['id']);
                            $browseable = false;

                        }
                        break;
                    case "RoonGoer":
                        if (file_exists('/share' . $arrTemp['id'] . '/Database/Registry') && is_dir('/share' . $arrTemp['id'] . '/Database/Registry')) {
                            $arrTemp['iconCls'] = 'fas fa-file-audio';
                            $arrTemp['id'] = dirname($arrTemp['id']);
                            $browseable = false;
                        }

                        break;
                }

                $arrShareTreeData[] = array(
                    'text' => $arrTemp['text'],
                    'path' => $arrTemp['id'],
                    'anychildren' => $browseable,
                    'faCssClass' => $arrTemp['iconCls']
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
    if (is_null($translations)) {
        $lang_file = QNAPLOCALDOC . '/cgi-bin/qpkg/RoonServer/l18n/locale-' . strtolower($_COOKIE['nas_lang']) . '.json';
        /* If no instance of $translations has occured load the language file */
        if (!file_exists($lang_file)) {
            $lang_file = QNAPLOCALDOC . '/cgi-bin/qpkg/RoonServer/l18n/locale-eng.json';
        }
        $lang_file_content = file_get_contents($lang_file);
        /* Load the language file as a JSON object
           and transform it into an associative array */
        $translations = json_decode($lang_file_content, true);
    }
    if (is_null($translations[$phrase])) {
        return $phrase;
    } else {
        return $translations[$phrase];
    }
}

function isRunning($pidfile, $option)
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
    $result["name"] = substr($r[0], strpos($r[0], ']: ') +3 );
    preg_match('/\[([\s\S])\w+/', $r[0], $matches, PREG_OFFSET_CAPTURE);
    $result["id"] = str_replace("[", "", $matches[0][0]);
    return $result;
}

function acardsNice()
{
    $arrSCards = GetAsoundCards();
    $strOutput = "";
    foreach ($arrSCards as &$value) {
        $strSCardName = $value['name'];
        $strSCardId = $value['id'];

        $strOutput = $strOutput . '<li class="list-group-item justify-content-between"><b>' . $strSCardId . '</b><br>' . $strSCardName . '</li>';

    }
    return $strOutput;
}

function downloadLogs($strSessionID, $dblocation)
{
    $dblength = strlen($dblocation);
    $folders = array($dblocation . '/RoonServer/Logs', $dblocation . '/RAATServer/Logs');

    $timestamp = new DateTime();
    $nice_timestamp = $timestamp->format('Ymd-His');


    $urlsnippet = "";
    $count = 0;

    foreach ($folders as $path) {
        $path = substr($path, $dblength+1);
        $urlsnippet = $urlsnippet . '&source_file=' . $path;
        $count++;

    }
    $url = QNAPDOCROOT . '/filemanager/utilRequest.cgi?func=download&sid=' . $strSessionID . '&isfolder=1&compress=0&source_path=' . $dblocation . '/' . $urlsnippet . '&source_total=' . $count;
    return $url;
}

function displayStorage($diskspace){
$bytes = $diskspace;
$si_prefix = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
$base = 1024;
$class = min((int)log($bytes , $base) , count($si_prefix) - 1);
//echo $bytes . '<br />';
return sprintf('%1.2f' , $bytes / pow($base,$class)) . ' ' . $si_prefix[$class];
}


?>