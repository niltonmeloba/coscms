<?php


/**
 * class for substituing youtube links with inline videos.
 */
class cosmedia {

    /**
     *
     * @param   string    string to filter.
     * @return  string    string with html with inline videos.
     */
    public static function filter($text){
        $text =  self::replaceYoutube($text);
        return $text;
    }

    public static function replaceYoutube ($text){
        $text = linkifyVimeo($text);
        $text = linkifyYouTubeURLs2($text);
        $text = linkifySoundcloud($text);
        return $text;
    }
    
    public static function videoRatio ($default = 600) {
        
        $width = config::getMainIni('media_width');
        if ($width) {
            return $ratio = $width / $default;
        } else {
            return 1;
        }
    }
}

class filters_cosmedia extends cosmedia {}

function linkifyVimeo ($text) {
    //$link = 'http://vimeo.com/10638288';
    $text = preg_replace_callback('~
        # Match non-linked youtube URL in the wild. (Rev:20111012)
        https?://         # Required scheme. Either http or https.
        vimeo\.com/      # or vimeo.com followed by
        (\d+)             # a number of digits
        ~ix',
            

            'vimeoCallback', 
            $text);
    return $text;


}

function linkifySoundcloud ($text) {

    $regex = '~https?://soundcloud\.com/[\-a-z0-9_]+/[\-a-z0-9_]+~ix';
    $text = preg_replace_callback($regex,
            'soundcloudCallback', 
            $text);
    return $text;
}

function soundcloudCallback ($match) {
    $url = $match[0];
    
    include_once "soundcloud.php";
    //$atts = 'soundcloud params="color=33e040&theme_color=80e4a0&iframe=true';
    $atts = array (
        'color' => '33e040',
        'theme_color' => '80e4a0',
        'iframe' => true
        
            );
    return soundcloud_shortcode($atts, $url);
}

function vimeoCallback ($text) {

    $ratio = cosmedia::videoRatio(400);
    $width =400;
    $height = 225;
    $width = ceil($ratio * $width);
    $height = ceil($ratio * $height);    
    $embed_code = $text[1];
    
    $str = <<<EOF
<div class="media_container">
<iframe 
    src="http://player.vimeo.com/video/$embed_code?title=0&amp;byline=0&amp;portrait=0"
    width="$width" 
    height="$height" 
    frameborder="0" 
    webkitAllowFullScreen mozallowfullscreen allowFullScreen>
</iframe>
</div>
EOF;
    return $str;
}

// Linkify youtube URLs which are not already links.
function linkifyYouTubeURLs2($text) {
    $text = preg_replace_callback('~
        # Match non-linked youtube URL in the wild. (Rev:20111012)
        https?://         # Required scheme. Either http or https.
        (?:[0-9A-Z-]+\.)? # Optional subdomain.
        (?:               # Group host alternatives.
          youtu\.be/      # Either youtu.be,
        | youtube\.com    # or youtube.com followed by
          \S*             # Allow anything up to VIDEO_ID,
          [^\w\-\s]       # but char before ID is non-ID char.
        )                 # End host alternatives.
        ([\w\-]{11})      # $1: VIDEO_ID is exactly 11 chars.
        (?=[^\w\-]|$)     # Assert next char is non-ID or EOS.
        (?!               # Assert URL is not pre-linked.
          [?=&+%\w]*      # Allow URL (query) remainder.
          (?:             # Group pre-linked alternatives.
            [\'"][^<>]*>  # Either inside a start tag,
          | </a>          # or inside <a> element text contents.
          )               # End recognized pre-linked alts.
        )                 # End negative lookahead assertion.
        [?=&+%\w\-]*      # Consume any URL (query) remainder.
        ~ix', 
        'youtubeCallback',
        $text);
    return $text;
}

function youtubeCallback ($text) {
    $embed_code = $text[1];
    $ratio = cosmedia::videoRatio(420);

    $width =420;
    $height = 315;
    $width = ceil($ratio * $width);
    $height = ceil($ratio * $height);

    
    $str = <<<EOF
<div class="media_container">
<iframe 
    width="$width" 
            height="$height"
    src="http://www.youtube.com/embed/$embed_code" 
    frameborder="0" 
    allowfullscreen>
</iframe>
</div>
EOF;
    return $str;
}



