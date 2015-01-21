<?php

// interim fix until someone changes the config.php on talks.php.net
if (!defined('PRES2_LOCALE')) { define('PRES2_LOCALE', 'en'); }
if (!defined('PRES2_LOCALEDIR')) { define('PRES2_LOCALEDIR', 'locale'); }
if (!defined('PRES2_USE_GETTEXT')) { define('PRES2_USE_GETTEXT', false); }
// after config.php has been changed the lines above can be removed

// set locale and bindings if we are using gettext
if (PRES2_USE_GETTEXT && extension_loaded('gettext')) {
    define('PRES2_GETTEXT_INIT', _init_gettext());
} else {
    define('PRES2_GETTEXT_INIT', false);
}

// always include the messages file, it is our backup
$lang_file = PRES2_LOCALEDIR.'/'.PRES2_LOCALE.'/LC_MESSAGES/pres2.php';
include_once $lang_file;

function _init_gettext() {
    // locale setting can be a pain
    // TODO need to see if there are missing locale aliases
    $locales_array = array(
        'en' => array('en_US', 'en_GB', 'en_AU', 'en_CA', 'english', 'C'),
        'es' => array('es_ES', 'es_PE', 'es_MX', 'es_AR', 'spanish'),
    );

    $loc = setlocale(LC_MESSAGES, $locales_array[PRES2_LOCALE]);
    if ($loc !== false) {
        putenv('LC_MESSAGES='.$loc);
    }
    $tdom = textdomain('pres2');
    $btex = bindtextdomain('pres2', PRES2_LOCALEDIR);
    return ($loc !== false) 
            && ($btex == realpath(PRES2_LOCALEDIR))
            && ($tdom == 'pres2');
}

function message($str) {
    // try using gettext if initiliazed
    if (PRES2_GETTEXT_INIT) {
        $trans = gettext($str);
        if ($trans != $str) { // only return something if gettext worked
            return $trans;
        }
    }
    // otherwise use the messages array
    $trans = $GLOBALS['messages'][$str];
    return ($trans == '' || is_null($trans)) ? $str : $trans;
}
?>
