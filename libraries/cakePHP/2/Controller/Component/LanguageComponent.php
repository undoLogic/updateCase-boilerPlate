<?php
//Created by undoLogic
//MIT

App::uses('Component', 'Controller');

/**
 *
 *
Add to the view / layout
<a href="<?= $this->webroot.$this->params['url']; ?>?Lang=EN" <?php if ($lang == 'en'): ?>class="active"<?php endif; ?>>English</a>
<a href="<?= $this->webroot.$this->params['url']; ?>?Lang=FR" <?php if ($lang == 'fr'): ?>class="active"<?php endif; ?>>French</a>
<a href="<?= $this->webroot.$this->params['url']; ?>?Lang=ES" <?php if ($lang == 'sp'): ?>class="active"<?php endif; ?>>Spanish</a>
 *
 *
 *
 * Class LanguageComponent
 * usage
 * in app_controller

add to core
Configure::write('Config.language', 'eng');

$components = array('Language');

//Add to App_controller

function setupLanguage() {
//language stuff
//does get info exist, this will be priority
if (isset($_GET[ 'Lang' ])) {
$this->Language->setGet($_GET[ 'Lang' ]);
}
//if there params of what language we should be using
if (isset($this->params[ 'language' ])) {
$this->Language->setParams($this->params[ 'language' ]);
}
//or we are going to check out session of cookie for a already selected language
$this->Language->setSession(
$this->Session
);
//$this->Language->setCookie($this->Cookie);
//and fall back to the default if not set yet
$this->Language->setDefaultLanguage(Configure::read('Config.language'));
$currLang = $this->Language->currLang();

//pr ($currLang);exit;
switch ($currLang) {
case 'fre':
$this->set('langFR', TRUE);
$this->set('lang', 'fr');
$this->set('currLang', $currLang);
//$this->Cookie->write('currLang', 'fre', NULL, '+350 day');
Configure::write('Config.language', 'fre');
Configure::write('UpdateCase.language', 'fre');
break;

case 'spa':
$this->set('langSP', TRUE);
$this->set('lang', 'sp');
$this->set('currLang', $currLang);
//$this->Cookie->write('currLang', 'es-mx', NULL, '+350 day');
Configure::write('Config.language', 'es-mx');
Configure::write('UpdateCase.language', 'es-mx');
break;

default:
$this->set('lang', 'en');
$this->set('langEN', TRUE);
$this->set('currLang', $currLang);
//$this->Cookie->write('currLang', 'eng', NULL, '+350 day');
Configure::write('Config.language', 'eng');
Configure::write('UpdateCase.language', 'eng');
}
}
 *
 */
class LanguageComponent extends Component {

    var $get = FALSE;
    var $params = FALSE;
    var $session = FALSE;
    var $variations = array(
        'EN' => 'eng',
        'FR' => 'fre',
        'en' => 'eng',
        'fr' => 'fre',
        'eng' => 'eng',
        'fre' => 'fre',
        'en-us' => 'eng',
        'fr-ca' => 'fre',
        '1' => 'fre',
        '' => 'eng',
        'es-mx' => 'spa',
        'spa' => 'spa',
        'es' => 'spa',
        'ES' => 'spa'

    );
    var $defaultLang = 'eng';

    var $debug = true;

    function debug($name) {
        $this->writeToLog('debug', $name, true);
    }

    public function writeToLog($filename, $message, $newLine = true) {
        if ($newLine) {
            $message = "\n".date('Ymd-His').' > '.$message;
        } else {
            $message = ' > '.$message;
        }
        file_put_contents(APP.'tmp/logs/'.$filename.'.log', $message, FILE_APPEND);
    }

    function reset() {
        //pr ($this->session->read('currLang'));
        //exit;
        $this->session->write('currLang', false);
    }

    function currLang() {

        $activeLang = '';

        //if the session was saved
        $sessionLang = $this->getSessionLang();
        //pr ('ddd'.$sessionLang);exit;
        if (!empty($sessionLang)) {
            $activeLang = $sessionLang;
            $this->debug('session: '.$sessionLang);
        }

        //lowest priority
        if (!empty($this->get)) {
            $activeLang = $this->variations($this->get);
            $this->debug('get: '.$this->get);
        }

        //browser currently set to
        if (!empty($this->params)) {
            $activeLang = $this->variations($this->params);
            $this->debug('activeparams: '.json_encode($this->params));
        }

        $this->setSessionLang($activeLang);

        //fall back to the browser setting
        if (empty($activeLang)) {
            $this->debug('default lang');
            return $this->defaultLang;
        } else {
            $this->debug($activeLang);
            return $activeLang;
        }


    }

    ////////////////////////////////////// setters
    function setGet($get) {
        $this->get = $this->variations($get);
    }

    function setParams($params) {
        $this->params = $this->variations($params);
    }

    function setSession($session) {
        $this->session = $session;
    }

    function setSessionLang($currlang) {
        $this->session->write('currLang', $this->variations($currlang));
    }
    function getSessionLang() {
        $sessionLang = $this->session->read('currLang');
        if (!empty($sessionLang)) {
            return $this->variations[$sessionLang];
        } else {
            return false;
        }
    }

    function setDefaultLanguage($defaultLang) {
        if (isset($this->variations[ $defaultLang ])) {
            $this->defaultLang = $this->variations[ $defaultLang ];
        }
    }

    ///////////////////////////////////getters

    private function variations($name) {
        if (isset($this->variations[ $name ])) {
            return $this->variations[ $name ];
        }
    }

}