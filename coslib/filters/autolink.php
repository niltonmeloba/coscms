<?php


/**
 * autolinking filter
 */


class autolink {

    /**
     * filter method
     * @param strin $text to filter
     * @return string $text
     */
    public static function filter($text){        
       $text = self::auto_link_text($text);
       return $text;
    }
    
   /**
    * // found on: http://stackoverflow.com/questions/1925455/how-to-mimic-stackoverflow-auto-link-behavior
    * // from http://daringfireball.net/2009/11/liberal_regex_for_matching_urls
    * Replace links in text with html links
    *
    * @param  string $text
    * @return string
    */
   public static function auto_link_text($text)
   {
      $pattern  = '#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#';
      $callback = create_function('$matches', '
          $url       = array_shift($matches);
          $url_parts = parse_url($url);

          $text = parse_url($url, PHP_URL_HOST) . parse_url($url, PHP_URL_PATH);
          $text = preg_replace("/^www./", "", $text);

          $last = -(strlen(strrchr($text, "/"))) + 1;
          if ($last < 0) {
              $text = substr($text, 0, $last) . "&hellip;";
          }

          return sprintf(\'<a target="_blank" href="%s">%s</a>\', $url, $text);
      ');

      return preg_replace_callback($pattern, $callback, $text);
    }
}

/**
 * added for autoloading
 */
class filters_autolink extends autolink {}
