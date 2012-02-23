<?php


/**
 * @package    coslib
 */

/**
 * set include path
 * @ignore
 */
$ini_path = ini_get('include_path');
ini_set('include_path', 
    _COS_PATH . PATH_SEPARATOR . 
    _COS_PATH . '/vendor' . PATH_SEPARATOR .
    _COS_PATH . "/coslib" . PATH_SEPARATOR . _COS_PATH . '/modules' . 
        $ini_path . PATH_SEPARATOR);



/**
 * include base classes and functions
 * @ignore
 */

include_once "coslib/config.php";
include_once "coslib/file.php";
include_once "coslib/strings.php";
include_once "coslib/db.php";
include_once "coslib/uri.php";
include_once "coslib/moduleLoader.php";
include_once "coslib/session.php";
include_once "coslib/html.php";
include_once "coslib/layout.php";
include_once "coslib/template.php";
include_once "coslib/event.php";
include_once "coslib/mail.php";
include_once "coslib/validate.php";
include_once "coslib/http.php";
include_once "coslib/user.php";
include_once "coslib/log.php";
include_once "coslib/lang.php";
include_once "coslib/time.php";

// set some common register vars
config::$vars['coscms_base'] = _COS_PATH;
config::$vars['coscms_main'] = array();
config::$vars['coscms_main']['module'] = array();
config::$vars['coscms_debug'] = array();
config::$vars['coscms_lang'] = array();
config::$vars['coscms_debug']['timer']['start'] = microtime(true);
config::$vars['coscms_debug']['coscms_base']  = config::$vars['coscms_base'];
config::$vars['coscms_debug']['include_path'] = ini_get('include_path');

// Rest is only for web mode. 
if (!defined('_COS_CLI')){
    
    config::loadMain();
    
    // set a unified server_name if not set in config file. 
    $server_name = config::getMainIni('server_name');
    if (!$server_name){
        config::setMainIni('server_name', $_SERVER['SERVER_NAME']);
    }
       
    ob_start();

    // redirect to uniform domain name is set in config.ini
    // e.g. www.testsite.com => testsite.com
    $server_redirect = config::getMainIni('server_redirect');
    if (isset($server_redirect)) {
        http::redirectHeaders($server_redirect);
    }
    
    // redirect to https is set in config.in
    // force anything into ssl mode
    $server_force_ssl = config::getMainIni('server_force_ssl');
    if (isset($server_force_ssl)) {
        http::sslHeaders();
    }
    
    // start session
    session::initSession();

    // init module loader
    $db = new db();
    $moduleLoader = new moduleLoader();
    $moduleLoader->runLevel(1);

    // select all db settings and merge them with ini file settings
    $db_settings = $db->selectOne('settings', 'id', 1);

    config::$vars['coscms_main'] =
        array_merge(config::$vars['coscms_main'] , $db_settings);
    
    // run level 2: Just after configuration from file have been set
    // in order to change e.g. file settings you can change the now.
    // See module configdb for example. 
    $moduleLoader->runLevel(2);

    if (isset(config::$vars['coscms_main']['locale'])){
        $locale = config::$vars['coscms_main']['locale'];
    } else {
        $locale = config::$vars['coscms_main']['language'].'.UTF8';
    }

    // set locale for time and monetary
    setlocale(LC_TIME, $locale);
    setlocale(LC_MONETARY, $locale);
    
    // set default timezone
    date_default_timezone_set(config::$vars['coscms_main']['date_default_timezone']);
    $moduleLoader->runLevel(4);

    // load languages.
    lang::init();    
    $moduleLoader->runLevel(5);

    // set files to load and init module.
    $moduleLoader->setModuleFiles();
    $moduleLoader->initModule();

    // include template class found in htdocs/templates
    // only from here we should use template class. 
    $layout = new layout();

    // we first load menus here so we can se what happened when we
    // inited module. In case of a 404 not found error we don't want
    // to load module menus
    $layout->loadMenus();
    
    // init blocks
    $layout->initBlocks();

    // load page module
    // catch the included controller file with ob functions
    // and return the parsed page as html
    $str = $moduleLoader->loadModule();
    
    // collect final out put
    ob_start();    
    mainTemplate::printHeader();
    echo $str;
    
    $moduleLoader->runLevel(6);
    mainTemplate::printFooter();   
    config::$vars['final_output'] = ob_get_contents();
    ob_end_clean();
    
    // Last divine intervention
    // tidy / e.g. Dom

    $moduleLoader->runLevel(7); 
    echo config::$vars['final_output'];
}
