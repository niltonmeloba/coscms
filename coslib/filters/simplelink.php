<?php

/**
 * file contains class for linkifing url to links
 * @package filters
 */

/**
 * file contains class for linkifing url to links
 * only difference from autolink is that it speaks danish .)
 * @package filters
 */

class simplelink {

    /**
     * transform urls to links
     * @param strin $text to filter
     * @return string $text
     */
    public static function filter($text){
       $text = self::autolink($text);
       return $text;
    }

    /**
     * do the filtering
     * @param string $text
     * @return string $text
     */
    public static function autolink ($text) {
        
        $text = " " . $text;
        $text = preg_replace("#([\n ])([a-zæøå]+?)://([a-zæøå0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+]+)#i", "\\1<a href=\"\\2://\\3\" target=\"_blank\">\\2://\\3</a>", $text);
        $text = preg_replace("#([\n ])www\.([a-zæøå0-9\-]+)\.([a-zæøå0-9\-.\~]+)((?:/[a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+]*)?)#i", "\\1<a href=\"http://www.\\2.\\3\\4\" target=\"_blank\">www.\\2.\\3\\4</a>", $text);
        $text = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)?[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $text);
        $text = substr($text, 1);
        return($text);
    }

}

/**
 * override only for easy autoloading
 * @package filters
 */
class filters_simplelink extends simplelink {}
