<?php
if (!isset($_COOKIE['NAS_USER']) || empty($_COOKIE['NAS_USER'])) {
    die("not logged in! ;)");
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

function getTreeRoot($strSessionID)
{
    $strUrl = NASLOCALHOST . '/cgi-bin/filemanager/utilRequest.cgi?func=get_tree&sid=' . $strSessionID . '&is_iso=0&node=share_root';
    $arrData = get_web_page($strUrl);
    $arrShareTree = json_decode($arrData['content'], 1);
    $arrShareTreeData = array();

    foreach ($arrShareTree as $arrTemp) {
        if (
            substr($arrTemp['text'], 0, 1) != "@" &&
            substr($arrTemp['text'], 0, 1) != "." &&
            substr($arrTemp['text'], 0, 2) != ".." &&
            $arrTemp['text'] != "home" &&
            $arrTemp['text'] != "homes"
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
    shell_exec(APPINSTALLPATH . '/Roonserver.sh start');
}

function getTreeAt($folder, $strSessionID)
{
    $folder = str_replace('+', '%20', filter_var($folder, FILTER_SANITIZE_STRING));
    if (strlen($folder) > 3) {
        $strUrl = NASLOCALHOST . '/cgi-bin/filemanager/utilRequest.cgi?func=get_tree&sid=' . $strSessionID . '&is_iso=0&node=' . $folder;
        $arrData = get_web_page($strUrl);
        $arrShareTree = json_decode($arrData['content'], 1);

        $arrShareTreeData = array();

        foreach ($arrShareTree as $arrTemp) {
            if (
                substr($arrTemp['text'], 0, 1) != "@" &&
                substr($arrTemp['text'], 0, 1) != "." &&
                substr($arrTemp['text'], 0, 2) != ".."
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
    $default_file = $_SERVER['DOCUMENT_ROOT'] . '/cgi-bin/qpkg/RoonServer/i18n/locale-eng.json';
    if (is_null($translations)) {
        $lang_file = $_SERVER['DOCUMENT_ROOT'] . '/cgi-bin/qpkg/RoonServer/i18n/locale-' . NAS_LANG . '.json';
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
    $dbLength = strlen($dblocation);
    $folders = array($dblocation . '/RoonServer/Logs', $dblocation . '/RAATServer/Logs', $dblocation . '/ROONSERVER_QNAP_LOG.txt');

    $urlSnippet = "";
    $count = 0;

    foreach ($folders as $path) {
        $path = substr($path, $dbLength+1);
        $urlSnippet = $urlSnippet . '&source_file=' . $path;
        $count++;

    }
    $url = NASHOST . '/cgi-bin/filemanager/utilRequest.cgi?func=download&sid=' . $strSessionID . '&isfolder=1&compress=2&source_path=' . $dblocation . $urlSnippet . '&source_total=' . $count;
    echo $url;
    //return $url;
}

function displayStorage($diskspace){
$bytes = $diskspace;
$si_prefix = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
$base = 1024;
$class = min((int)log($bytes , $base) , count($si_prefix) - 1);
return sprintf('%1.2f' , $bytes / pow($base,$class)) . ' ' . $si_prefix[$class];
}


?>