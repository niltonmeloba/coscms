<?php

/**
 * File containing template class. 
 * 
 * @package coslib
 */

/**
 * simple template class for cos cms
 * abstract because it will always be extended by mainTemplate
 * which will be used for display the page
 * 
 * @package coslib
 */
abstract class template {
    
    /**
     * @var array   holding css files
     */
    static $css = array();

    /**
     * @var array   holding js files
     */
    static $js = array();

    /**
     * @var array   holding inline js strings
     */
    static $inlineJs = array();

    /**
     * @var array   holding inline css strings
     */
    static $inlineCss = array();

    /**
     * @var string  holding meta tags
     */
    static $meta = array();

    /**
     * @var string  holding title of page being parsed
     */
    static $title = '';

    /**
     * @var string   holding last html strings
     */
    static $endHTML = '';

    /**
     * @var string   holding end of content string
     */
    static $endContent = '';
    
    /**
     *
     * @var string  $templateName 
     */
    static $templateName = null;
    
    /**
     * 
     * @var string $cacheDir name of dir where we cache assets 
     * 
     */
    public static $cacheDir = 'cached_assets';

    /**
     * will be set in init
     * @var string $cacheDir name of dir where we cache assets 
     * 
     */
    public static $cacheDirWeb = '';
    
    /**
     * method for setting title of page
     * @param string $title the title of the document
     */
    public static function setTitle($title){
        self::$title = html::specialEncode($title);
    }

    /**
     * method for getting title of page
     * @return string   $title title of document
     */
    public static function getTitle(){
        return self::$title;
    }

    /**
     * method for setting meta tags. The tags will be special encoded
     * @param   array   $ary of metatags e.g. 
     *                  array('description' => 'content of description meta tags')
     */
    public static function setMeta($ary){
        foreach($ary as $key => $val){
            if (isset(self::$meta[$key])){
                continue;
            }
            self::$meta[$key] = html::specialEncode($val);
        }
    }
    
    /**
     * method for getting html for front page. If no logo has been 
     * uploaded. You will get logo as html
     * @param type $options options to give to html::createHrefImage
     * @return string $str the html compsoing the logo or main title
     */
    public static function getLogoHTML ($options = array()) {
        $logo = config::getMainIni('logo');
        if (empty($logo)){
            return $str = "<div id=\"logo_title\"><a href=\"/\">$_SERVER[HTTP_HOST]</a></div>";
        } else {
            $file ="/logo/" . config::$vars['coscms_main']['logo'];
            $src = config::getWebFilesPath($file);
            if (!isset($options['alt'])){           
                $options['alt'] = $_SERVER['HTTP_HOST'];
            }
            $href = html::createHrefImage('/', $src, $options);
            $str = '<div id="logo_img">' . $href . '</div>' . "\n"; 
            //die($str);
            return $str;
        }
    }

    /**
     * method for getting the meta tags as a string
     * You can specifiy meta keywords and description global in config.ini
     * by using the settings, meta_desc and meta_keywords.
     *  
     * @return string $str the meta tags as a string. This can be used
     *                     in your mainTemplate
     */
    public static function getMeta (){        
        $str = '';

        if (!isset(self::$meta['keywords'])) {
            $str = '';
            $str = config::getMainIni('meta_keywords');
            $str = trim($str);
            if (!empty($str)) {
                self::$meta['keywords'] = $str;
            }
        }

        if (empty(self::$meta['description'])) {
            $str = '';
            $str = config::getMainIni('meta_desc');
            $str = trim($str);
            if (!empty($str)) {
                self::$meta['description'] = $str;
            }
        }

        $str = '';
        foreach (self::$meta as $key => $val) {
            $str.= "<meta name=\"$key\" content=\"$val\" />\n";
        }

        return $str;
    }

    /**
     * method for setting css files to be used on page
     *
     * @param string    string css_url pointing to the css on your server e.g. /templates/module/good.css
     * @param int       loading order. 0 is loaded first and > 0 is loaded later
     */
    public static function setCss($css_url, $order = null, $options = null){
        if (isset($order)){
            if (isset(self::$css[$order])) {
                self::setCss($css_url, $order + 1, $options);
            } else {
                self::$css[$order] = $css_url;
            }
        } else {
            self::$css[] = $css_url;
        }
    }


    /**
     * method for getting css for displaing in user template
     * @return  string  the css as a string
     */
    public static function getCss(){
        
        $str = "";
        ksort(self::$css);
        
        foreach (self::$css as $key => $val){
            $str.= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$val\" />\n";
        }
        
        return $str;
    }
    
    /**
     * Will load the js as file and place and add it to array which can
     * be parsed in user templates.
     * 
     * @param   string   $js file path of the javascript
     * @param   int      $order the loading order of javascript 0 is first > 0 is
     *                   later.
     */
    public static function setStringJs($js, $order = null, $options = array()){
        
        /*
        if (config::getMainIni('cached_assets') && !isset($options['no_cache'])) {
            self::cacheAsset ($js, $order, 'js');
            return;
        }*/
        
        //$str = file_get_contents($js);
        if (isset($options['search'])){
            $js = str_replace($options['search'], $options['replace'], $str);
        }
        
        //$js = "<script>$js</script>\n";
        //var myvar = <?php echo json_encode($myVarValue); 
        if (isset($order)){
            self::$inlineJs[$order] = $js;
        } else {
            self::$inlineJs[] = $js;
        }
    }


    /**
     * method for setting js files to be used by user templates. This is
     * used with javascripts which are placed in web space.
     * @param   string   $url pointing to the path of the javascript
     * @param   int      $order. the loading order of javascript 0 is first > 0 is
     *                   later.
     * $param   array    $options
     */
    public static function setJs($js_url, $order = null, $options = null){
        
        if (isset($order)){
            if (isset(self::$js[$order])) {
                self::setJs($js_url, $order + 1, $options);
            } else {
                self::$js[$order] = $js_url;
            }
        } else {
            self::$js[] = $js_url;
        }
    }

    /**
     * method for getting css files used in user templates
     * @return  string  the css as a string
     */
    public static function getJs(){
        $str = "";
        ksort(self::$js);
        foreach (self::$js as $key => $val){
            $str.= "<script src=\"$val\" type=\"text/javascript\"></script>\n";
        }
        return $str;
    }
    
    /**
     * Will load the js as file and place and add it to array which can
     * be parsed in user templates. This is used with js files that exists
     * outside webspace, e.g. in modules
     * 
     * @param   string   $js file path of the javascript
     * @param   int      $order the loading order of javascript 0 is first > 0 is
     *                   later.
     */
    public static function setInlineJs($js, $order = null, $options = array()){
        
        if (config::getMainIni('cached_assets') && !isset($options['no_cache'])) {
            self::cacheAsset ($js, $order, 'js');
            return;
        }
        
        $str = file_get_contents($js);
        if (isset($options['search'])){
            $str = str_replace($options['search'], $options['replace'], $str);
        }
        
        if (isset($order)){
            if (isset(self::$inlineJs[$order])) {
                self::$inlineJs[] = $str;
            } else {
                self::$inlineJs[$order] = $str;
            }
        } else {
            self::$inlineJs[] = $str;
        }
    }

    /**
     * method for getting all inline js as a string
     * @return  string  $str the js as a string
     */
    public static function getInlineJs($section = null){
        $str = "";
        ksort(self::$inlineJs);
        foreach (self::$inlineJs as $key => $val){
            if (isset($section)) {
                
            }
            
            $str.= "<script type=\"text/javascript\">$val</script>\n";
        }
        return $str;
    }

    /**
     * method for setting user css used inline in user templates.
     *
     * @param   string   $str string file path of the css
     * @param   int      $order. the loading order of css 0 is first > 0 is
     *                   later.
     */
    public static function setInlineCss($css, $order = null, $options = array()){

        if (config::getMainIni('cached_assets') && !isset($options['no_cache'])) {
            self::cacheAsset ($css, $order, 'css');
            return;
        }
          
        $str = file_get_contents($css);
        if (method_exists('mainTemplate', 'assetsReplace')) {
            $str = mainTemplate::assetsReplace($str);
        }
                
        if (isset($order)){
            self::$inlineCss[$order] = $str;
        } else {
            self::$inlineCss[] = $str;
        }
    }

    
    /**
     * method for caching a asset (js or css)
     * @param type $css
     * @param type $order
     * @param type $type 
     */
    private static function cacheAsset ($css, $order, $type) {
        static $cacheChecked = false;
        
        if (!$cacheChecked) {
            self::$cacheDirWeb = config::getWebFilesPath(self::$cacheDir);
            self::$cacheDir = config::getFullFilesPath() . '/' . self::$cacheDir;
            if (!file_exists(self::$cacheDir)) {
                mkdir(self::$cacheDir);
            }  
            $cacheChecked = true;
        }
        
        $md5 = md5($css);        
        $cached_asset = config::getFullFilesPath() . "/cached_assets/$md5.$type";
        $cache_dir = config::getWebFilesPath('/cached_assets');
        if (file_exists($cached_asset && !config::getMainIni('cached_assets_reload'))) {
            
            if ($type == 'css') {
                self::setCss("$cache_dir/$md5.$type", $order);
            }
            
            if ($type == 'js') {
                self::setJs("$cache_dir/$md5.$type", $order);
            }          
        } else {
            $str = file_get_contents($css); 
            if (method_exists('mainTemplate', 'assetsReplace')) {
                $str = mainTemplate::assetsReplace($str);
            }
            
            file_put_contents($cached_asset, $str);

            if ($type == 'css') {
                self::setCss("$cache_dir/$md5.$type", $order);
            }
            
            if ($type == 'js') {
                self::setJs("$cache_dir/$md5.$type", $order);
            } 
        }
    }
    
    /**
     * method for parsing a css file and substituing css var with
     * php defined values
     * @param string $css
     * @param array  $vars
     * @param int    $order
     */
    public static function setParseVarsCss($css, $vars, $order = null){
        $str = get_include_contents($css, $vars);
        //$str = file_get_contents($css);
        if (isset($order)){
            self::$inlineCss[$order] = $str;
        } else {
            self::$inlineCss[] = $str;
        }
    }

    /**
     * method for getting css used in inline in user templates
     * @return  string  the css as a string
     */
    public static function getInlineCss($section = null){
        $str = "";
        ksort(self::$inlineCss);
        foreach (self::$inlineCss as $key => $val){
            $str.= "<style type=\"text/css\">$val</style>\n";
        }
        return $str;
    }

    /**
     * method for adding string to end of html
     * @param   string  string to add to end of html
     */
    public static function setEndHTML($str){
        self::$endHTML.=$str;
    }

    /**
     * method for getting end of html
     * @return  string  end of html
     */
    public static function getEndHTML(){
        return self::$endHTML;
    }

    /**
     * method for setting end html
     * @param string    end content
     */
    public static function setEndContent($str){
        self::$endContent.=$str;
    }

    /**
     * method for getting end of html
     * @return <type>
     */
    public static function getEndContent(){
        return self::$endContent;
    }
    
    /**
     * inits a template
     * set template name and load init settings
     * @param string $template name of the template to init. 
     */
    public static function init ($template) {       
        self::$templateName = $template;
        if (!isset(config::$vars['template'])) {
            config::$vars['template'] = array();
        }       
        moduleLoader::setModuleIniSettings($template, 'template');
    }
    
    
    /**
     * checks if a css style is registered. If not
     * we use common.css in template folder.
     * 
     * @param string $template
     */
    public static function setTemplateCss ($template = '', $order = 0, $version = 0){

        $css = config::getMainIni('css');
        $css_path = "/templates/$template/$css/$css.css";
        $css_url = $css_path . "?version=$version";
        $css_file = _COS_PATH . '/htdocs' . $css_path;
        if (file_exists($css_file)){ 
            self::setCss($css_url, $order);
        } else {
            // use default css
            self::setCss("/templates/$template/default/default.css?version=$version", $order);
        }
    }
}
/**
 * class with simple template methods
 * @package coslib
 */

class templateView {

    /**
     * default view folder
     * @var string $viewFolder default view folder in a module folder
     */
    static $viewFolder = 'views';

    /**
     * function for including a view file.
     * Maps to module (e.g. 'tags' and 'view file' e.g. 'add')
     * we presume that views are placed in modules views folder
     * e.g. tags/views And we presume that views always has a .inc
     * postfix
     *
     * @param string $module
     * @param string $file
     * @param array  $vars to parse into template
     * @param boolean return as string (1) or output directly (0) 
     */
    static function includeModuleView ($module, $view, $vars = null, $return = null){
        $filename = _COS_PATH . "/modules/$module/" . self::$viewFolder . "/$view.inc";

        if (is_file($filename)) {
            ob_start();
            include $filename;
            $contents = ob_get_contents();
            ob_end_clean();
            if ($return) {
                return $contents;
            } else {
                echo $contents;
            }
        } else {
            echo "View: $filename not found";
            return false;
        }
    }
    
    /**
     * shorthand for includeModuleView. Will always return the parsed template 
     * instead of printing to standard output. 
     * 
     * @param string $module the module to include view from
     * @param string $view the view to use
     * @param mixed $vars the vars to use in the template
     * @return string $parsed the parsed template view  
     */
    public static function get ($module, $view, $vars = null) {
        return self::includeModuleView($module, $view, $vars, 1);
    }
}

/**
 * function for including a view file.
 * Maps to module (e.g. 'tags' and 'view file' e.g. 'add')
 * we presume that views are placed in modules views folder
 * e.g. tags/views And we presume that views always has a .inc
 * postfix
 *
 * @param string $module the module where our view exists
 * @param string $file the view file we want to use
 * @param mixed $vars vars to substitue in view
 * @param boolean $return if true we will return the content of the view
 *                        if false we echo the view
 */
function include_view ($module, $view, $vars = null, $return = null){
    return templateView::includeModuleView($module, $view, $vars, 1); 
}

/**
 * function for getting content from a file
 * used as a very simple template function
 * @deprecated
 * @param string $filename the full path of the file to include
 * @param mixed  $vars the var to sustitute with
 * @return string $str the parsed template.
 */
function get_include_contents($filename, $vars = null) {
    if (is_file($filename)) {
        ob_start();
        include $filename;
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }
    return false;
}
