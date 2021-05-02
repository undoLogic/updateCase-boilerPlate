<?php
//App::uses('Folder', 'Utility');
//App::uses('File', 'Utility');
//App::uses('HttpSocket', 'Network/Http');

date_default_timezone_set('UTC');
//VERSION 19.09.06
/**
 * Instructions how to setup
 * Download this file from UpdateCase.com
 */
$token = 'CHANGE-ME';
$pathJsonFile = '../Config/Schema/';

function writeToLog($message, $newLine = false)
{

    if (is_array($message)) {
        $message = implode("\n", $message);
    }

    if ($newLine) {
        $message = "\n" . date('Ymd-His') . ' > ' . $message;
    } else {
        $message = ' > ' . $message;
    }
    file_put_contents('updateCase.log', $message, FILE_APPEND);

    //echo APP.'tmp/logs/'.$type;
}

//writeToLog('client receiving GET ' . json_encode($_GET));

//external communcations
if (isset($_POST['token'])) {
    if ($_POST['token'] != $token) {
        writeToLog('BAD TOKEN');

        die ('405: NO ACCESS');
    } else {
        if (isset($_GET['test'])) {
            if ($_GET['test'] == 'true') {
                echo 'access granted';
            }
        }

        if (isset($_GET['version'])) {
            if ($_GET['version'] == 3) {
                writeToLog('V3');

                $decoded = json_decode($_POST['variant']);
                //$uuid = $decoded->Variant->uuid;
                $uuid = $decoded[0]->Variant->uuid;
                $variant_id = $decoded[0]->Variant->id;

                if (empty($variant_id)) $variant_id = 'unknown';

                $pathJsonFile = $pathJsonFile . $variant_id . '/';

                if (!file_exists($pathJsonFile)) {
                    mkdir($pathJsonFile);
                }

                $myfile = fopen($pathJsonFile . $uuid . ".json", "w") or die("Unable to open file!");
                fwrite($myfile, $_POST['variant']);
                fclose($myfile);

                echo 'IMPORTED';
                writeToLog('Imported');
            } else {
                echo 'Command not recognized';
                writeToLog('Command not recognized');
            }
        } else {
            writeToLog('NO VERSION SUPPLIED');
        }
    }
} else {
    //if this being accessed directly

    //////////////////// Only process php scripts ///////////////////////
    $pathInfo = pathinfo($_SERVER['REQUEST_URI']);
    if (isset($pathInfo['extension'])) {
        $extension = $pathInfo['extension'];
        $tmp = explode('?', $extension);
        if ($tmp[0] != 'php') {
            //die('Stopping for ext: '.$pathInfo['extension']);
        }
    }


    writeToLog('accessed directly', false);
    //writeToLog(json_encode($_SERVER['REQUEST_URI']), true);
    //writeToLog(json_encode(pathinfo($_SERVER['REQUEST_URI'])), true);
    //	if (isset($_SERVER)) {
    //		if (strpos($_SERVER['SCRIPT_NAME'], 'updateCase.php') !== false) {
    //			//setupLocal();
    //		}
    //	}
}

//internal communications
// Put class TestClass here

class UpdateCase
{
    // loads the available json and
    function loadPageBySlug($slug, $debug = false)
    {

        if (!$this->isPrepared()) {
            die('Not prepared');
        } else {
            //load our page
            foreach ($this->pages as $page) {

                //pr ($page['slug']);

                if (strtolower($page['slug']) == 'all') {
                    $this->allPages = $page; //do we need this ?
                    $this->writeToLog('loaded all: ' . $slug);
                    //return true;
                }
                if (trim($page['slug']) == trim($slug)) {
                    $this->page = $page;

                    if ($debug) {
                        pr ($this->page);exit;
                    }

                    $this->writeToLog('loaded page: ' . $page['slug']);

                }
            }


            $this->language = Configure::read('UpdateCase.language');
            if (empty($this->language)) {
                $this->language = $this->possibleLanguages['eng'];
            } else {
                $this->language = $this->possibleLanguages[$this->language];
            }
        }
    }

    public function getContentBy($locationName, $elementName, $groupName = false, $slug = false)
    {
        if ($slug) {
            $this->loadPageBySlug($slug);
        }

        if (!$this->isPrepared()) {
            return false;
        } else {
            if ($groupName == 'false') $groupName = false;

            if (empty($this->page)) {
                $this->writeToLog('Page not setup', true);
                return false;
            }

            //pr ($locationName.' elm: '.$elementName.' gr: '.$groupName);

            $element = $this->getElement($locationName, $elementName, $groupName);
            //@todo add is location active

            //pr ($element);exit;

            if (isset($element['name'])) {
                if ($element['name'] == 'image') {
                    return true;
                } else {
                    return $element['Revision'][0]['content_text'];
                }
            } else {
                return false;
            }

        }

    }

    public function getImageBy($location, $element, $group = false, $size = 'medium', $slug = false)
    {
        if ($slug) {
            $this->loadPageBySlug($slug);
        }

        if ($group == 'false') {
            $group = false;
        }
        //pr ($this->page);
        //return false;

        //APP.'webroot'.DS.
        $cache = 'images' . DS . Configure::read("UpdateCase.variant_id") . DS;

        //exec('ls', $oupt);
        //pr($oupt);
        //exit;
        //die ($cache);
        $element = $this->getElement($location, $element, $group);

        if (!isset($element['Revision'][0])) {
            $this->writeToLog('No revision for the image');
            return false;
        } else {
            $mime = $element['Revision'][0]['mime'];
            $id = $element['Revision'][0]['id'];

            //pr ($mime);
            //exit;

            if ($mime == 'image/jpeg') {
                $filename = $id . '.jpg';
            } elseif ($mime == 'image/png') {
                $filename = $id . '.png';
            } else {
                //pr ($this->revision);
                $this->writeToLog('cannot load image', true);
                //echo $message;
                //exit;
                return false;
            }
            //pr ($cache.$filename);exit;
            //does a cached version exist

            $this->writeToLog('open image: ' . $cache . $filename);

            //exit;
            // pr ($filename);exit;

            if (file_exists($cache.$filename)) {
                //return the local file
                return $cache . $filename;
            } else {
                //create the file locally
                $this->writeToLog('create folder: ' . $cache);

                if (!file_exists($cache)) {
                    //die ('not exists '.$cache);
                    mkdir($cache);
                }

                if (!file_exists($cache)) {
                    $this->writeToLog('Image Cache missing: ' . $cache);
                } else {
                    $imageLink = $this->host.'imagesGet/' . $id . '/' . $size . '/pic.jpg';
                    $this->writeToLog('Writing image: ' . $imageLink . ' to ' . $cache . $filename);
                    file_put_contents($cache.$filename, file_get_contents($imageLink));
                    return $cache . $filename;
                }
            }
        }


    }

    public function getFileBy($location, $element, $group = false, $slug = false)
    {
        if ($slug) {
            $this->loadPageBySlug($slug);
        }

        $cache = 'images' . DS . Configure::read("UpdateCase.variant_id") . DS;

        if ($group == '') {
            $group = false;
        }
        //pr ($element);
        $id = $this->getIdBy($location, $element, $group);

        if (!$id) {
            $message = 'File cannot load | Location: ' . $location . ' / Element ' . $element . ' / Group:' . $group;
            $this->writeToLog($message, true);
            return false;
        }

        //pr ($id);
        //pr ($this->revision);exit;
        //pr ($id);
        //pr ($element);exit;

        //pr ($this->revision);

        $element = $this->getElement($location, $element, $group);

        $revision = $element['Revision'][0];


        //pr ($id);
        if ($revision['mime'] == 'application/pdf') {
            $filename = $id . '.pdf';
        } elseif ($revision['mime'] == 'application/epub') {
            $filename = $id . '.epub';
        } elseif ($revision['mime'] == 'application/mobi') {
            $filename = $id . '.mobi';
        } elseif ($revision['mime'] == 'application/octet-stream') {
            $filename = $id . '.mobi';
        } elseif ($revision['mime'] == 'image/jpeg') {
            $filename = $id . '.jpg';
        } else {

            //echo 'cannot load slug';
            //pr ($this->revision);
            //pr ($id);exit;

            //exit;
            //pr ($this->revision);
            //$message = 'File cannot load | SLUG: ' . $this->slug . ' / Location: ' . $location . ' / Element ' . $element . ' / Group:' . $group;
            //$this->writeToLog($message, true);
            //echo $message;
            //exit;
            //return $message;
            return false;
        }

        //pr ($filename);
        //does a cached version exist
        $file = new File($cache . $filename);

        // pr ($filename);exit;

        if ($file->exists()) {
            //return the local file


            return $cache . $filename;
        } else {
            //create the file locally

            $dir = new Folder($cache, true, 0775);

            if (file_exists($cache)) {
                //$imageLink = 'http://files.setupcase.com/display/' . $id . '/file.png';
                $imageLink = 'http://site.updatecase.com/display/' . $id . '/file.png';
                $file->write(file_get_contents($imageLink));
                return $cache . $filename;
            } else {
                //something went wrong with creating the folder, so let's just return the link from our server
                //$imageLink = 'http://files.setupcase.com/display/' . $id . '/file.png';
                $imageLink = 'http://site.updatecase.com/display/' . $id . '/file.png';
                return $imageLink;
            }
        }
    }











    //////////////////////////////////////////////////// OLD below ///////////////////////////
    var $host = "http://site.updatecase.com/";
    function isPrepared($autoDetectDebug = true)
    {
        $this->language = Configure::read('UpdateCase.language');
        if (empty($this->language)) {
            $this->language = $this->possibleLanguages['eng'];
        } else {
            $this->language = $this->possibleLanguages[$this->language];
        }

        //exit;
        if ($autoDetectDebug) {
            //die('hi');
            $debug = $this->getDebugStatus();
            //pr ('debug: '.$debug);exit;

            if ($debug == 2) {
                $this->setState("TEST");
            } else {
                $this->setState("PROD");
            }
        } else {
            //manual mode - do not auto change
        }

        //exit;

        //pr ($this->state);exit;

        //do we have a uuid file
        if ($this->getProdOrTestState() == 'TEST') {
            //die('tttttest');
            //exit;
            //we are test mode - let's see if there is a more recent version
            $this->writeToLog('TEST MODE - ensure most recent', TRUE);

            //exit;
            //we are TEST so try to get the most recent
            $this->local_uuid = $this->downloadFromUpdateCase($this->getVariantId());
            $this->getMostRecentFile($this->getVariantId());
            return $this->prepareJson($this->getVariantId(), $this->local_uuid);

        } elseif ($this->getProdOrTestState() == 'PROD') {
            //we are LIVE, let's use what we have
            $this->writeToLog('PROD', TRUE);
            $this->getMostRecentFile($this->getVariantId());

            return $this->prepareJson($this->getVariantId(), $this->local_uuid);

        } else {
            die ('UNKNOWN STATE');
        }
    }

    private function prepareJson($variant_id)
    {

        if (!$this->local_uuid) {
            $this->writeToLog('404: no local uuid specified', true);
            return false;
        } else {
            if (empty($this->json)) {
                //first time let's get our data
                $this->writeToLog('Prepare JSON (first time only)');

                $this->jsonPath = APP . 'Config' . DS . 'Schema' . DS . $variant_id;
                $data = file_get_contents($this->jsonPath . DS . $this->local_uuid . '.json');

                $this->json = json_decode($data, true);

                $this->writeToLog('Decoded json');

                $this->site = $this->json[0]['Site'];
                $this->variant = $this->json[0]['Variant'];
                $this->pages = $this->json[0]['Page'];
                $this->page = array();

                $this->writeToLog(count($this->pages). ' pages loaded');


            } else {
                $this->writeToLog('JSON already loaded', TRUE);
            }
            return true;
        }
    }

    function reset($variant_id = false, $uuid = false) {

        if ($uuid) {
            unlink(APP.'Config'.DS.'Schema'.DS.$variant_id.DS.$uuid.'.json');
        }

        $this->site = array();
        $this->variant = array();
        $this->pages = array();
        $this->page = array();
        $this->json = array();

        $this->writeToLog('RESET');
    }

    public function getMostRecentFile($variant_id = false, $reverse = false)
    {
        $this->jsonPath = APP . 'Config' . DS . 'Schema' . DS . $variant_id;

        $files = scandir($this->jsonPath);
        foreach ($files as $fileKey => $file) {
            $ext = substr($file, -4);
            if ($ext != 'json') {
                unset($files[$fileKey]);
            }
        }

        //pr ($files);
        //die("hello");

        //@todo do we need this ?
        foreach ($files as $key => $file) {
            if (strlen($file) < 20) { //we don't want to use the older manual name of sites
                unset($files[$key]);
            }
        }

        if (empty($files)) {
            $this->writeToLog('NO LOCAL JSON - we must download it');
            $this->local_uuid = $this->downloadFromUpdateCase($variant_id);
            return $this->local_uuid;

        } else {
            $this->writeToLog('found: ' . count($files) . ' file(s)');
            sort($files);
            $newestFile = end($files);
            $newestFile = str_replace(".json", '', $newestFile);
            $this->local_uuid = $newestFile;
            $this->writeToLog('file: ' . $newestFile, false);
            return $newestFile;

        }
    }

    private function downloadFromUpdateCase($variant_id, $current_uuid = false)
    {
        $this->jsonPath = APP . 'Config' . DS . 'Schema' . DS;

        $pathToUse = $this->hostPath . 'Variants/getCurrentUUID/' . $variant_id;
        $this->writeToLog('get UUID only from updateCase: ' . $pathToUse, false);

        $server_uuid = file_get_contents($pathToUse);

        if (empty($server_uuid)) {
            $this->writeToLog('404: uuid not avail', false);
            return false;
        } else {

            if ($current_uuid == $server_uuid) {
                //we are already up to date
                $this->writeToLog('200: Already up to date', false);
                return $current_uuid;
            } else {

                $pathToUse = $this->hostPath . 'Variants/get/' . $variant_id;
                $this->writeToLog('get full content updateCase: ' . $pathToUse, false);

                $newJsonContent = file_get_contents($pathToUse);
                $folder = $this->jsonPath . $variant_id;

                if (!file_exists($folder)) {
                    mkdir($folder);
                }

                $locationToWrite = $folder . DS . $server_uuid . '.json';
                $this->writeToLog('Writing to : ' . $locationToWrite);
                file_put_contents($locationToWrite, $newJsonContent);
                $this->writeToLog('Downloaded NEW JSON: ' . $server_uuid, false);

                return $server_uuid;
            }
        }
    }

    private function getVariantId()
    {

        $this->variant_id = Configure::read("UpdateCase.variant_id");

        if (!$this->variant_id) {
            $this->writeToLog('Missing Variant_id', true);
        } else {
            $this->writeToLog('variant_id: ' . $this->variant_id, false);
            return $this->variant_id;
        }
    }

    var $local_uuid = FALSE;
    var $variant_id = FALSE;


    function getRecentFile()
    {
        if (!$this->local_uuid) {
            //get the most recent file
        } else {

        }
    }

    function getProdOrTestState()
    {

        //die ('getting: '.$this->state);
        return $this->state;
    }

    function setState($state)
    {
        $allowed = array(
            'PROD' => 'PROD',
            'TEST' => 'TEST'
        );
        if (isset($allowed[$state])) {
            $this->state = $allowed[$state];
            $this->writeToLog('STATE set to: ' . $this->state, TRUE);
        }
    }

    //////////////// old
    var $json = '';
    var $jsonPath = '';
    var $state = 'UNKNOWN';

    var $jsonData = array(); //all the json data
    var $hostPath = 'http://site.updatecase.com/';

    var $uuid = '';

    var $variant = array();
    var $site = array();
    var $pages = array();
    var $page = array(); //this is the page information

    var $allPages = array();

    var $language = '';
    var $possibleLanguages = array(
        'eng' => 'eng',
        'en-us' => 'eng',
        'en-ca' => 'eng',
        'eng' => 'eng',
        'fr-ca' => 'fre',
        'fre' => 'fre',
        'fra' => 'fre',
        'ALL' => 'ALL'
    );

    function isCurrentLanguage($fieldLang) {

        $setLang = Configure::read('UpdateCase.language');

        if (isset($this->possibleLanguages[ $setLang ])) {
            $setLang = $this->possibleLanguages[ $setLang ];
        }

        if (isset($this->possibleLanguages[ $fieldLang ])) {
            $fieldLang = $this->possibleLanguages[ $fieldLang ];
        }

        if ($setLang == $fieldLang) {
            return true;
        } else {
            return false;
        }

    }

    var $localUUID = '';


    function getLocalUUID()
    {
        return $this->local_uuid;
    }

    function isLocationActive($currLocationName) {

        //pr ($this->page);exit;
        foreach ($this->page['Location'] as $location) {
            if ($currLocationName == $location['name']) {
                if (
                    (strtotime('now') > strtotime($location['date_active']))
                    AND
                    (strtotime('now') < strtotime($location['date_expire']))
                ) {
                    return true;
                }
            }
        }
        //die('false');
        return false;
    }

    private function decideDebug()
    {
        $debug = $this->getDebugStatus();
        if (!$debug) {
            return false;
        } else {
            //our debug is on so we might want to turn it off again
            $lastTimeDebugEdited = filemtime(APP . 'Config' . DS . 'core.php');
            $now = strtotime('now');
            $diff = $now - $lastTimeDebugEdited;
            if ($diff > 3600) { //it has been 15 minutes since we saved our file
                $this->writeToLog('Turning OFF debug');
                $this->setDebugOff();
            } else {
                $this->writeToLog('NOT Turning OFF debug YET (still time left before auto off)');
            }
        }
    }

    ///////////////////////////////////////////////////////////////////////////// AUTO DEBUG ///
    var $on_message = "Configure::write('debug',2);";
    var $off_message = "Configure::write('debug',0);";

    function setDebugOff()
    {

        //this will set the debug to
        $path_to_file = APP . 'Config';
        $file_contents = file_get_contents($path_to_file . DS . 'core.php');
        //print_r ($file_contents);exit;

        $file_contents = $this->turnOffDebug($file_contents);
        if ($file_contents) {
            //let's save it
            file_put_contents($path_to_file . DS . 'core.php', $file_contents);
            //$this->Session->setFlash('Debug mode is now OFF');
            //echo 'Debug mode is now OFF';

        } else {
            // echo 'Debug mode already off';
        }

    }

    function setDebugOn()
    {


        //this will set the debug to
        $path_to_file = APP . 'Config';
        $file_contents = file_get_contents($path_to_file . DS . 'core.php');
        //print_r ($file_contents);exit;

        $file_contents = $this->turnOnDebug($file_contents);
        if ($file_contents) {
            //let's save it
            file_put_contents($path_to_file . DS . 'core.php', $file_contents);
            //$this->Session->setFlash('Debug mode is now OFF');
            //echo 'Debug mode is now OFF';

        } else {
            // echo 'Debug mode already off';
        }

    }

    function getDebugStatus()
    {

        $path_to_file = APP . 'Config';
        $file_contents = file_get_contents($path_to_file . DS . 'core.php');
        //debug($file_contents);exit;
        $pos = strpos($file_contents, $this->on_message);
        if ($pos === false) {
            //there is no on message, let's check if there is an off

            $pos_off = strpos($file_contents, $this->off_message);
            if ($pos_off === false) {
                //there is a problem,manual intervention is required
                $msg = 'manual intervention required the debug message needs to be exactly: ' . $this->off_message;
                $this->writeToLog($msg);
                die ($msg);
            } else {
                //it's ok, there is an off, so let's repalce it
                return 'OFF';
            }
        } else {
            return 'ON';
        }
    }

    private function turnOnDebug($contents)
    {

        //if the debug mode is on then return same string
        //if the debug mode if off, replace the contents
        //        $on_message = "Configure::write('debug',2);";
        //        $off_message = "Configure::write('debug',0);";
        $pos = strpos($contents, $this->on_message);
        if ($pos === false) {
            //there is no on message, let's check if there is an off
            $pos_off = strpos($contents, $this->off_message);
            if ($pos_off === false) {
                //there is a problem,manual intervention is required
                $msg = 'manual intervention required the debug message needs to be exactly: ' . $this->off_message;
                $this->writeToLog($msg);
                die ($msg);

            } else {
                //it's ok, there is an off, so let's repalce it
                $contents_modified = str_replace($this->off_message, $this->on_message, $contents);
                return $contents_modified;
            }
        } else {
            //it's already on
            return false;
        }
    }

    private function turnOffDebug($contents)
    {

        //if the debug mode is on then return same string
        //if the debug mode if off, replace the contents

        $pos = strpos($contents, $this->off_message);
        if ($pos === false) {
            //there is no off message, let's check if there is an on
            $pos_off = strpos($contents, $this->on_message);
            if ($pos_off === false) {
                //there is a problem,manual intervention is required
                $msg = 'manual intervention required the debug message needs to be exactly: ' . $this->on_message;
                $this->writeToLog($msg);
                die ($msg);

            } else {
                //it's ok, there is an off, so let's repalce it
                $contents_modified = str_replace($this->on_message, $this->off_message, $contents);
                return $contents_modified;
            }
        } else {
            //it's already on
            return false;
        }
    }


    public function doesSlugExist($slug)
    {

        foreach ($this->pages as $page) {
            if ($page['slug'] == $slug) {
                return true;
            }
        }
        return false;
    }

    /**
     * getting content without loading
     */
    private function getByWithoutLoading($slug, $location_to_check, $element_to_check, $lang = false)
    {

        if (!$this->isPrepared()) {
            return false;
        } else {

            if ($lang) {

            } else {
                $lang = $this->convertToLongLang[Configure::read("UpdateCase.language")];
            }

            if (empty($lang)) {
                $msg = 'Missing in APP_CONTROLLER: Configure::write("UpdateCase.language", "eng")';
                $this->writeToLog($msg);

            }
            //pr ($lang);exit;

            //get the page
            foreach ($this->pages as $page) {
                if ($page['slug'] == $slug) {


                    foreach ($page['Location'] as $location) {
                        if ($location['name'] == $location_to_check) {

                            foreach ($location['Element'] as $element) {
                                if ($element['name'] == $element_to_check) {


                                    if ($element['language'] == $lang) {
                                        if (isset($element['Revision'][0])) {
                                            return trim($element['Revision'][0]['content_text']);
                                        }
                                    }
                                }
                            }
                        }
                    }

                }

            }
        }
    }


    public function getPageSlugsByTag($tagName, $sortBy = 'DATE-ASC', $ensureAllTags = false)
    {

        $pageNames = array();

        $sort = array();
        $available = '';
        //get the page

        //pr ($this->pages);exit;


        //pr ($tagName);
        //exit;

        $pagesWithTags = array();

        foreach ($this->pages as $keyPage => $page) {

            if (!empty($page['Tag'])) {
                //loop through our tags
                //if any are missing not match
                if (is_array($tagName)) {
                    foreach ($tagName as $eachTagName) {
                        foreach ($page['Tag'] as $pageTag) {
                            if ($pageTag['name'] == $eachTagName) {
                                $pagesWithTags[$page['slug']]['tag'][$eachTagName] = $eachTagName;
                                $pagesWithTags[$page['slug']]['date'] = $page['date'];
                            }
                        }
                    }
                } else {
                    foreach ($page['Tag'] as $pageTag) {
                        if ($pageTag['name'] == $tagName) {
                            $pagesWithTags[$page['slug']]['tag'][$tagName] = $tagName;
                            $pagesWithTags[$page['slug']]['date'] = $page['date'];

                        }
                    }
                }


            }
        }

        if ($ensureAllTags) {
            foreach ($pagesWithTags as $slug => $eachPageWithTags) {
                //we need all our tags, so unset the pages that do not have both
                if (is_array($tagName)) {
                    foreach ($tagName as $eachTagName) {
                        if (!isset($eachPageWithTags['tag'][$eachTagName])) {
                            unset($pagesWithTags['tag'][$slug]);
                        }
                    }
                } else {
                    if (!isset($eachPageWithTags['tag'][$tagName])) {
                        unset($pagesWithTags['tag'][$slug]);
                    }
                }
            }
        }






        //pr ($sortBy);
        //exit;
        //pr ($pagesWithTags);
        //exit;
        if ($sortBy == 'ASC') {
            //sort by the date which is the key
            ksort($pagesWithTags);
        } else if ($sortBy == 'DESC') {
            krsort($pagesWithTags);
        } else if ($sortBy == 'DATE-ASC') {
            uasort($pagesWithTags, function ($item1, $item2) {
                if ($item1['date'] == $item2['date']) return 0;
                return $item1['date'] > $item2['date'] ? -1 : 1;
            });
        } else if ($sortBy == 'DATE-DESC') {
            //pr ('here');
            uasort($pagesWithTags, function ($item1, $item2) {
                if ($item1['date'] == $item2['date']) return 0;
                return $item1['date'] > $item2['date'] ? -1 : 1;
            });
        }

        //pr ($pagesWithTags);
        //exit;
        $return = array();
        foreach ($pagesWithTags as $slug => $tags) {
            $return[$slug] = $slug;
        }
        return $return;
    }

    private function cleanUpStringForQuotedSections($str)
    {
        return str_replace('"', "'", $str);
    }


    //do we need this ? it is really that must faster ?????
    private function doesNewerExist($variant_id, $newestUuid)
    {
        $HttpSocket = new HttpSocket();

        $pathToUse = $this->hostPath . 'public/variants/uuid/' . $variant_id . '/' . $newestUuid;

        //pr ($pathToUse);exit;
        $this->writeToLog('get file from updateCase: ' . $pathToUse, true);

        $response = $HttpSocket->post($pathToUse, array(
            'token' => Configure::read('updateCase.token'),
        ));

        //pr ($response->body);
        //exit;
        if (empty($response->body)) {
            $this->writeToLog('we have current file', false);
            return false;
        } else {
            $tmp = explode(":", $response->body);
            switch ($tmp[0]) {
                case '200':
                    return false;
                    break;
                default:
                    return true;
            }
        }
    }


    private function getElement($locationName, $elementName, $groupName = false)
    {

        $debug = false;


        if ($debug) pr ('loc: '.$locationName.' elm:'.$elementName.' gr:'.$groupName);
        //exit;

        if (!$this->isPrepared()) {
            return false;
        } else {

            $this->writeToLog('GetElement: ' . $locationName . ':' . $elementName . ':' . $groupName, true);

            if (isset($this->page['Location'])) {
                foreach ($this->page['Location'] as $location) {
                    if ($location['name'] == $locationName) {
                        $this->writeToLog('location matches: ' . $locationName);
                        foreach ($location['Element'] as $element) {

                            $use = false;
                            if ($element['name'] == $elementName) {
                                if ($groupName) {
                                    if ($element['groupBy'] == $groupName) {
                                        $use = true;
                                    }
                                } else {
                                    if (empty($element['groupBy'])) {
                                        $use = true;
                                    }

                                }
                            }

                            if ($use) {
                                //pr ($use);
                                //pr($element);exit;

                                if ($element['language'] == 'ALL') {
                                    return $element;
                                } else if ($this->isCurrentLanguage($element['language'] )) {
                                    return $element;
                                } else {
                                    //not a ALL lang and not in our currentd lange so try the next loop
                                }
                            }
                        }
                    }
                }
            }

            //pr ('trying all');

            $this->writeToLog('No specific element found, lets check the all');

            //pr ($this->allPages);exit;
            foreach ($this->allPages['Location'] as $location) {
                if ($location['name'] == $locationName) {

                    if ($debug) pr ('good loc: '.$locationName);
                    foreach ($location['Element'] as $element) {
                        $use = false;

                        if ($element['name'] == $elementName) {
                            if ($groupName) {
                                if ($element['groupBy'] == $groupName) {
                                    $use = true;
                                }
                            } else {
                                $use = true;
                            }
                        }

                        //pr ($use);exit;
                        if ($use) {

                            if ($element['language'] != 'ALL') {
                                return $element;
                            } else if ($this->isCurrentLanguage($element['language'] )) {
                                return $element;
                            } else {

                            }
                        }
                    }
                }
            }

            $this->writeToLog('No Element found: ' . $locationName . ' ' . $elementName . ' ' . $groupName);
            return false;
        }

    }

    public function getTagsFromAllPages($ignore = array()) {
        //pr ($this->pages);

        $allTags = array();
        foreach ($this->pages as $this->page) {
            $allTags = $allTags + $this->getTags($ignore);
        }
        return $allTags;

    }
    public function getTags($ignore = array()) {
        //pr ($this->page);

        if (!$this->isPrepared()) {
            return false;
        } else {

            $tags = array();
            if (isset($this->page['Tag'])) {
                foreach ($this->page['Tag'] as $tag) {


                    if (in_array($tag['name'], $ignore)) {
                        //ignore
                    } else {
                        $tags[$tag['name']] = $tag['name'];
                    }
                }
            }

        }

        //pr ($tags);
        return $tags;
        //exit;
    }



    public function getIdBy($locationName, $elementName, $groupName = false)
    {

        if ($groupName == 'false') $groupName = false;

        if (empty($this->page)) {
            $this->writeToLog('Page not setup', true);
            return false;
        }

        $element = $this->getElement($locationName, $elementName, $groupName);
        //@todo add is location active

        if (isset($element['Revision'])) {
            return $element['Revision'][0]['id'];
        } else {
            return false;
        }
    }

    public function getMetaTitle()
    {
        //do we have a set slug


        $title = '';
        $slug = Configure::read("UpdateCase.slug");


        if (!empty($slug)) { //we have a page specific
            if ($this->doesSlugExist($slug)) {
                $title = $this->cleanUpStringForQuotedSections($this->getByWithoutLoading($slug, $this->seoLocationName, 'title'));
            }
        }
        if (empty($title)) {

            if ($this->doesSlugExist('All')) {
                $title = $this->cleanUpStringForQuotedSections($this->getByWithoutLoading('All', $this->seoLocationName, 'title'));
            }
        }

        return $title;

        //do we have a all page with meta
        //return false;
    }

    public function getMetaDescription()
    {

        $field = '';
        $slug = Configure::read("UpdateCase.slug");

        if (!empty($slug)) { //we have a page specific
            if ($this->doesSlugExist($slug)) {
                $field = $this->cleanUpStringForQuotedSections($this->getByWithoutLoading($slug, $this->seoLocationName, 'description'));
            }
        }
        if (empty($title)) {

            if ($this->doesSlugExist('All')) {
                $field = $this->cleanUpStringForQuotedSections($this->getByWithoutLoading('All', $this->seoLocationName, 'description'));
            }
        }
        return $field;
    }

    public function getMetaKeywords()
    {
        $field = '';
        $slug = Configure::read("UpdateCase.slug");

        if (!empty($slug)) { //we have a page specific
            if ($this->doesSlugExist($slug)) {
                $field = $this->cleanUpStringForQuotedSections($this->getByWithoutLoading($slug, $this->seoLocationName, 'keywords'));
            }
        }
        if (empty($title)) {

            if ($this->doesSlugExist('All')) {
                $field = $this->cleanUpStringForQuotedSections($this->getByWithoutLoading('All', $this->seoLocationName, 'keywords'));
            }
        }
        return $field;
    }

    public function getMetaProperty($name)
    {
        $desc = '';

        //do we have a set slug
        $slug = Configure::read("UpdateCase.slug");
        if (!empty($slug)) { //we have a page specific
            if ($this->doesSlugExist($slug)) {
                $desc = $this->cleanUpStringForQuotedSections($this->getByWithoutLoading($slug, $this->seoLocationName, $name));
            }
        }

        if (empty($desc)) {
            if ($this->doesSlugExist('All')) {
                $desc = $this->cleanUpStringForQuotedSections($this->getByWithoutLoading('All', $this->seoLocationName, $name));
            }
        }

        return $desc;

        //do we have a all page with meta
        //return false;
    }

    //OG
    public function getMetaOgLocale($lang)
    {


        $send = array(
            'en' => 'en_CA',
            'fr' => 'fr_CA',
            'eng' => 'en_CA',
            'fre' => 'fr_CA'
        );
        if (isset($send[$lang])) {
            return $send[$lang];
        }
        return false;
    }

    public function getMetaOgLocaleAlternate($lang)
    {
        $send = array(
            'eng' => 'fr_CA',
            'en' => 'fr_CA',
            'fre' => 'en_CA',
            'fr' => 'en_CA'
        );
        if (isset($send[$lang])) {
            return $send[$lang];
        }
        return false;
    }

    public function getMetaOgUrl($webroot, $params)
    {

        return $webroot . $params->url;

        //pr ($webroot);
        //pr ($params);
        //pr ($webroot. ltrim($params->here, '/'));exit;
    }

    public function getMetaOgSiteName()
    {
        //do we have a set slug

        $title = '';
        $slug = Configure::read("UpdateCase.slug");


        if (!empty($slug)) { //we have a page specific
            if ($this->doesSlugExist($slug)) {
                $title = $this->cleanUpStringForQuotedSections($this->getByWithoutLoading($slug, $this->seoLocationName, 'og-site_name'));
            }
        }
        if (empty($title)) {

            if ($this->doesSlugExist('All')) {
                $title = $this->cleanUpStringForQuotedSections($this->getByWithoutLoading('All', $this->seoLocationName, 'og-site_name'));
            }
        }

        return $title;

        //do we have a all page with meta
        //return false;
    }

    public function getMetaOgImage($webroot = false)
    {
        //do we have a set slug

        $imageUrl = '';
        $slug = Configure::read("UpdateCase.slug");


        if (!empty($slug)) { //we have a page specific
            if ($this->doesSlugExist($slug)) {
                $this->loadPageBySlug($slug);
                $image = $this->getImage('SEO', 'og-image');
            }
        }
        if (empty($title)) {
            $this->loadPageBySlug('All');

            if ($this->doesSlugExist('All')) {
                $image = $this->getImage('SEO', 'og-image');
            }
        }

        if ($webroot) {
            $imageUrl = $webroot . $image;
        } else {
            $imageUrl = $image;
        }
        //$title = str_replace("<img src='", '', $title);
        // $title = str_replace("' />", '', $title);
        return $imageUrl;

        //do we have a all page with meta
        //return false;
    }

    var $seoLocationName = 'SEO';


    var $convertToLongLang = array(
        'eng' => 'en-ca',
        'fre' => 'fr-ca'
    );

    public function getPageSlugsByTagWithLocationElement($tagName, $sortBy = 'ASC', $location, $element, $group = false, $limit = false, $offset = false, $options = false)
    {

        $pageNames = array();
        //$this->page = false;

        $sort = array();

        //get the page

        //pr ($this->pages);exit;

        foreach ($this->pages as $keyPage => $page) {


            // pr ($page);

            if (!$this->existsInPage($page['slug'], $location, $element, $group)) {
                //die ('does not exist');
                continue;
            }


            //pr ($this->language);exit;


            $pagesHasStuffForThisLanguage = false;
            //let's ensure we have the following location / element
            foreach ($page['Location'] as $tierLocation) {
                foreach ($tierLocation['Element'] as $tierElement) {

                    //pr ($tierElement);exit;

                    if ($this->language == $this->possibleLanguages[$tierElement['language']]) {
                        $pagesHasStuffForThisLanguage = true;
                    }
                }
            }

            //skip this page it has not element of this language
            if (!$pagesHasStuffForThisLanguage) continue;

            if (!empty($page['Tag'])) {
                foreach ($page['Tag'] as $tag) {

                    if (is_array($tagName)) {
                        if (in_array($tag['name'], $tagName)) {
                            //this tag is present
                            $sort[$page['slug']] = strtotime($page['date']);
                        }
                    } else {
                        if ($tag['name'] == $tagName) {
                            //this tag is present
                            $sort[$page['slug']] = strtotime($page['date']);
                        }
                    }
                }
            }
        }

        //die('after');


        if ($sortBy == 'ASC') {
            //sort by the date which is the key
            asort($sort);
        } else {
            arsort($sort);
        }

        foreach ($sort as $slug => $num) {
            $pageNames[$slug] = $slug;
        }

        if ($options) {
            if ($options == 'SHUFFLE') {

                $keys = array_keys($pageNames);
                shuffle($keys);
                foreach ($keys as $key) {
                    $new[$key] = $pageNames[$key];
                }
                $pageNames = $new;
            }

        }
        //pr ($pageNames);exit;

        $this->total = count($pageNames);

        if (empty($pageNames)) {
            return array();
//            $message = 'Tag not found: ' . $tagName;
//            return $this->missingMessage($message);
            exit;
        }

        if (!$limit) {
            return $pageNames;
        } else {
            $pageNames = array_slice($pageNames, (($offset - 1) * $limit), $limit);
            return $pageNames;
        }

        //pr ($pageNames);
        //exit;

    }


    public function getImageAltTag($location, $element, $group = false, $size = 'medium')
    {
        if ($size != 'medium') {
            $alt = 'alt="' . $location . '-' . $element . '-' . $group . '-' . $size;
        } else {
            $alt = 'alt="' . $location . '-' . $element . '-' . $group;
        }
        $alt = rtrim($alt, '-');
        $alt .= '"'; //close the hyphen

        return $alt;
    }


    public function getImage($location, $element, $group = false, $size = 'medium') {
        return $this->getImageBy($location, $element, $group, $size);
    }



    private function prepareTranslation($element, $term)
    {


        $translations = $this->getByWithoutLoading('All', 'Translations', $element, 'en-ca');
        //pr ($translations);

        //echo 'trans'.$translations.'222';
        //exit;

        if (!empty($translations)) {
            //pr ($this->getByWithoutLoading('All', 'Translations', $element));
            $title = $this->cleanUpStringForQuotedSections($translations);
            //pr ($title);exit;
        } else {
            //die ('no transl');
            //no translation
            return $term;
        }
        $title = str_replace("\n", "<-->", $title);
        $title = str_replace("<br><br/>", "<-->", $title);
        $title = str_replace("<br />", "<-->", $title);
        $title = str_replace("<br/>", "<-->", $title);
        $title = trim($title);
        //echo $title;
        //exit;
        $tmp = explode("<-->", $title);
        //print_r ($tmp);exit;
        //pr ($tmp);
        //exit;

        foreach ($tmp as $eRow) {
            //print_r ($eRow);
            $tmp_term = explode(">", trim($eRow));
            //pr ($tmp_term);exit;
            if (strtolower(trim($tmp_term[0])) == strtolower(trim($term))) {
                return $tmp_term[1];
            }
        }

        //keep track if we do NOT have a translation and add to the file
        $this->keepTrackOfNewTranslations($term);

        return $term;
    }

    public function keepTrackOfNewTranslations($newWord) {
        //check if we already have this word
        $current = file_get_contents('updateCaseTranslations.txt');
        //pr ($current);
        $lines = explode("\n", $current);
        foreach ($lines as $line) {
            $check = str_replace(">", "", $line);
            if (strtoupper($newWord) == strtoupper($check)) {
                //already have it
                return false;
            } else {
                //save it
            }
        }

        if (empty($newWord)) {
            return false;
        }

        $current = $current . "\n".$newWord.'>';
        file_put_contents('updateCaseTranslations.txt', $current);
    }

    public function Translate($term, $element = 'en->fr')
    {
        if ($this->language == 'eng') {
            //do we have a en->en translations
            $translated = $this->prepareTranslation('en->en', $term);
        } else {
            $translated = $this->prepareTranslation($element, $term);
        }
        return $translated;
    }

    public function exists($locationName, $elementName = false, $groupName = false)
    {

        $debug = false;

        $this->writeToLog('Does location exist: ' . $locationName . ' element: ' . $elementName . ' gr:' . $groupName);

        //pr ($this->page['Location']);exit;
        foreach ($this->page['Location'] as $location) {
            //echo $locationName.' -> '.$location->name."<br/>";
            if ($locationName !== $location['name']) {
                continue;
            } else {

                if ($debug) pr ('Location: '.$location['name']);

                //pr ($location);exit;
                //the location matches

                if (!$elementName) {
                    //no element so let's return true
                    return true;
                } else {


                    //we are looking for an element
                    foreach ($location['Element'] as $element) {

                        if (!$this->isCurrentLanguage($element['language'] )) continue;

                        if ($elementName !== $element['name']) {

                            if ($debug) pr ('element do not match: '.$elementName.' '.$element['name']);

                            //echo 'does not '.$elementName;
                            //exit;
                            continue;
                        } else {

                            if ($debug) pr ('element MATCH: '.$elementName.' '.$element['name']);


                            //pr ($element);exit;

                            if (!$groupName) {
                                return true;
                            } else {

                                if ($element['groupBy'] === $groupName) {
                                    return true;
                                }
                            }

                        }

                    }

                }

            }

            return false;

        }


//            $quit = $this->setup($locationName, $elementName, $groupName);
//            if ($quit) {
//                return false;
//            }
//            if (isset($this->element->Revision[0])) {
//                return true;
//            } else {
//                return false;
//            }
    }

    public function isEvery($nth, $count)
    {
        //2
        if ($count == $nth) {
            return true;
        }
        return false;
    }


    public function getGroupNamesByLocation($locationName, $sort = 'ASC', $slug = false)
    {
        if ($slug) {
            $this->loadPageBySlug($slug);
        }

        $this->groupNames = array();

        foreach ($this->page['Location'] as $location) {
            if ($location['name'] == $locationName) {
                foreach ($location['Element'] as $element) {
                    if (empty($element['groupBy'])) {
                        //skip
                    } else {
                        $this->groupNames[$element['groupBy']] = $element['groupBy'];
                    }
                }
            }
        }

        if ($sort == 'ASC') {
            natsort($this->groupNames);
        } else {
            krsort($this->groupNames);
        }

        return $this->groupNames;

    }


    public function isNotEmpty($locationName, $elementName = false, $groupName = false)
    {

        //pr ($this->page);exit;
        $test = $this->getContentBy($locationName, $elementName, $groupName);

        //pr ($test);exit;
        if (!empty($test)) {
            return true;
        } else {
            return false;
        }
    }


    public function doesContain($search, $locationName, $elementName = false, $groupName = false)
    {

        //pr ($this->page);exit;
        $test = $this->getContentBy($locationName, $elementName, $groupName);

        if (!empty($test)) {
            if (strpos($test, $search) !== false) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    public function isEmpty($locationName, $elementName = false, $groupName = false)
    {

        //pr ($this->page);exit;

        $test = $this->getContentBy($locationName, $elementName, $groupName);

        if (empty($test)) {
            return true;
        } else {
            return false;
        }

    }

    public function getPageDate($format = 'Y-m-d H:i:s')
    {

        $date = strtotime($this->page['date']);
        $lang = Configure::read('UpdateCase.language');
        if ($lang == 'fre') {

            if ($format == 'Y') {
                return date($format, $date);
            } else {
                //french
                setlocale(LC_ALL, 'fr_FR.UTF-8');
                //echo date('D d M, Y');
                //return strftime("%a %d %b %Y", $date);
                return strftime("%e %B %Y", $date);
                //$shortDate = strftime("%d %b %Y", $date);
            }
        } else {
            return date($format, $date);
        }

    }


    var $total = 0;

    public function getTotalRecords()
    {
        return $this->total;
    }

    public function existsInPage($slug, $locationName, $elementName = false, $groupName = false)
    {

        //echo 'hi';
        //pr ($locationName);

        //pr ($elementName);

        // pr ($this->page);exit;

        $this->writeToLog('Does location exist: ' . $locationName . ' element: ' . $elementName . ' gr:' . $groupName);

        foreach ($this->pages as $page) {

            if ($slug != $page['slug']) {
                continue;
            }

            //pr ($this->page['Location']);exit;
            foreach ($page['Location'] as $location) {

                //echo $locationName.' -> '.$location->name."<br/>";
                if ($locationName != $location['name']) {

                    $this->writeToLog('Location does not match: ' . $locationName . ' / ' . $location['name']);
                    continue;
                } else {
                    $this->writeToLog('Matches: ' . $locationName . ' / ' . $location['name']);

                    //pr ($location);exit;
                    //the location matches

                    if (!$elementName) {
                        //no element so let's return true
                        return true;
                    } else {

                        //we are looking for an element
                        foreach ($location['Element'] as $element) {

                            //pr ($location->Element);

                            if ($elementName != $element['name']) {
                                //echo 'does not '.$elementName;
                                //exit;
                                continue;
                            } else {

                                //pr ($element);exit;

                                if (!$groupName) {
                                    return true;
                                } else {

                                    if ($element['groupBy'] == $groupName) {
                                        return true;
                                    }
                                }

                            }

                        }

                    }


                }

                return false;
            }
        }


//            $quit = $this->setup($locationName, $elementName, $groupName);
//            if ($quit) {
//                return false;
//            }
//            if (isset($this->element->Revision[0])) {
//                return true;
//            } else {
//                return false;
//            }
    }

    public function getPagesBySearch($search)
    {
        $results = array();

        $search = strtolower($search);
        $available = '';
        //get the page
        foreach ($this->pages as $page) {

            $tags = array();
            foreach ($page['Tag'] as $tag) {
                $tags[$tag['name']] = $tag['name'];
            }

            foreach ($page['Location'] as $location) {


                foreach ($location['Element'] as $element) {

                    foreach ($element['Revision'] as $revision) {

                        if (stripos($revision['content_text'], $search) !== false) {
                            //echo 'true';
                            $found = array(
                                'slug' => $page['slug'],
                                'tags' => implode(',', $tags),
                                'location' => $location['name'],
                                'element' => $element['name'],
                                'language' => $element['language'],
                                'text' => strip_tags($revision['content_text'])
                            );
                            $results[$page['slug']] = $found;
                        }

                        //pr ($revision);exit;
                    }
                }
            }

        }

        //pr ($results);
        if (!empty($results)) {
            return $results;
        } else {
            return false;
        }

    }

//
    public function getPageSlugsBySearch($search)
    {

        $results = array();

        $search = strtolower($search);

        $available = '';
        //get the page
        foreach ($this->pages as $page) {


            foreach ($page['Location'] as $location) {


                foreach ($location['Element'] as $element) {

                    foreach ($element['Revision'] as $revision) {

                        if (stripos($revision['content_text'], $search) !== false) {
                            //echo 'true';
                            $found = array(
                                'slug' => $page['slug'],
                                'location' => $location['name'],
                                'element' => $element['name'],
                                'language' => $element['language'],
                                'text' => strip_tags($revision['content_text'])
                            );
                            $results[$page['slug']] = $page['slug'];
                        }

                        //pr ($revision);exit;
                    }
                }
            }

        }


        //pr ($results);
        if (!empty($results)) {
            return $results;
        } else {
            return false;
        }

    }


    public function convertString($from, $to, $string)
    {
        foreach ($from as $kFrom => $vFrom) {
            $string = str_replace($vFrom, $to[$kFrom], $string);
        }
        //return "";
        return $string;
    }

    public function getFile($location, $element, $group = false) {
        return $this->getFileBy($location, $element, $group);
    }


    public function removeTextFrom($remove, $string)
    {
        return str_replace($remove, '', $string);
    }

    public function getLocationNames($ignore = false)
    {


        $this->count = 0;

        if (!$this->page) {
            $msg = 'Page not loaded';
            $this->writeToLog($msg);
            die ($msg);

        }

        foreach ($this->page['Location'] as $location) {
            if ($ignore == $location['name']) {
                //we want to ignore this location
            } else {
                $this->locationNames[$location['name']] = $location['name'];
            }
        }

        return $this->locationNames;
    }

    public function getUniqueNameForFieldByLocation($locationName, $field)
    {

        $categories = array();

        //pr ($this->page);exit;
        foreach ($this->page['Location'] as $location) {
            if ($location['name'] == $locationName) {
                foreach ($location['Element'] as $element) {

                    //pr ($element);exit;

                    if ($element['name'] == $field) {
                        //this is our field
                        //pr ($element);exit;
                        $categories[ str_replace(' ', '', $element['Revision'][0]['content_text']) ] = $element['Revision'][0]['content_text'];
                    }

                }
            }
        }

        return $categories;

    }



    public function ensureHttp($url) {
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "http://" . $url;
        }
        return $url;
    }

    private function writeToLog($message, $newLine = false)
    {
        if (is_array($message)) {
            $message = implode("\n", $message);
        }
        if ($newLine) {
            $message = "\n" . date('Ymd-His') . ' > ' . $message;
        } else {
            $message = ' > ' . $message;
        }
        file_put_contents('updateCase.log', $message, FILE_APPEND);

        //echo APP.'tmp/logs/'.$type;
    }


}
