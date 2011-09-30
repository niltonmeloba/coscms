<?php

/**
 * File containing documentation functions for shell mode
 *
 * @package     shell
 */

include_once "lib/curl.php";
include_once "lib/moduleloader.php";
// {{{ check_access($options)
/**
 * function for checking if your are denying people 
 * from e.g. admin areas of your module. 
 */
function test_access($options = null){
    
   $module_dir = _COS_PATH . "/modules";
   $files = get_file_list_recursive($module_dir, "*.php");
   
   $base_url = "http://" . get_main_ini('server_name');
   foreach ($files as $val) {
       $url = str_replace($module_dir, '', $val);
       $url = substr($url, 0, -4);
       
       $url = $base_url . $url;
       $curl = new mycurl($url);
       $curl->createCurl();
       
       
       echo $curl->getHttpStatus();
       echo cos_cli_print(" Status code recieved on: $url");       
   }
   
}
// }}}


mainCli::setCommand('test-access', array(
    'description' => 'Dev command for checking if allow people where they are not allowed. ',
));

mainCli::setOption('test_access', array(
    'long_name'   => '--check',
    'description' => 'Will enable current directory as an apache2 virtual host. Will also add new sitename to your /et/hosts file',
    'action'      => 'StoreTrue'
));

