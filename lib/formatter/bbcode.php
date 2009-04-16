<?
class BBCode extends BBCodeParse {////////////////////////////////////////////////////////////////////////////////

    function parse($output){
        return $this->bbcodes($output);    }

////////////////////////////////////////////////////////////////////////////////

}

////////////////////////////////////////////////////////////////////////////////

//
// Copyright (C) 2006-2008 2z project (http://2z-project.com)
// Name: parse.class.php
// Description: Parsing and formatting routines
// Author: 2z project team
//

class BBCodeParse {
    function slashes($content) {
        return (get_magic_quotes_gpc()) ? $content : addslashes($content);
    }

    function userblocks($content){
        global $config, $lang, $userROW;
        if (!$config['blocks_for_reg']) return $content;
        return preg_replace("#\[hide\]\s*(.*?)\s*\[/hide\]#is", is_array($userROW)?"$1":str_replace("{text}", $lang['not_logged'], $lang['not_logged_html']), $content);
    }


    // Scan URL and normalize it to convert to absolute path
    // Check for XSS
    function normalize_url($url){
        if (((substr($url,0,1) == "\"") && (substr($url,-1,1) == "\""))||
            ((substr($url,0,1) == "'")  && (substr($url,-1,1) == "'" ))) {
            $url = substr($url, 1, strlen($url)-2);
        }

        // Check for XSS attack
        $urlXSS = str_replace(array(ord(0), ord(9), ord(10), ord(13), ' ', "'", "\"", ";"),'',$url);
        if (preg_match('/^javascript:/is', $urlXSS)) {
            return false;
        }

        // Add leading "http://" if needed
        if (!preg_match("#^(http|ftp|https|news)\://#i", $url)) {
            $url = "http://".$url;
        }
        return $url;
    }

    // Parse BB-tag params
    function parseBBCodeParams($paramLine){

        // Start scanning
        // State:
        // 0 - waiting for name
        // 1 - scanning name
        // 2 - waiting for '='
        // 3 - waiting for value
        // 4 - scanning value
        // 5 - complete
        $state = 0;
        // 0 - no quotes activated
        // 1 - single quotes activated
        // 2 - double quotes activated
        $quotes = 0;

        $keyName = '';
        $keyValue = '';
        $errorFlag = 0;

        $keys = array();

        for ($sI = 0; $sI < strlen($paramLine); $sI ++) {
            // act according current state
            $x = $paramLine{$sI};

            switch ($state) {
                case 0:  if      ($x == "'") { $quotes = 1; $state = 1; $keyName = '';}
                         else if ($x == "'") { $quotes = 2; $state = 1; $keyName = ''; }
                         else if ((($x >='A')&&($x <='Z'))||(($x >='a')&&($x <='z'))) { $state = 1; $keyName = $x; }
                         break;
                case 1:  if ((($quotes == 1)&&($x == "'"))||(($quotes == 2)&&($x == '"'))) { $quotes = 0; $state=2; }
                         else if ((($x >='A')&&($x <='Z'))||(($x >='a')&&($x <='z'))) { $keyName .= $x; }
                         else if ($x == '=') { $state = 3; }
                         else if (($x == ' ')||($x == chr(9))) { $state = 2; }
                         else { $erorFlag = 1; }
                         break;
                case 2:  if ($x == '=') { $state = 3; }
                         else if (($x == ' ')||($x == chr(9))) { ; }
                         else { $errorFlag = 1; }
                         break;
                case 3:  if      ($x == "'") { $quotes = 1; $state = 4; $keyValue = '';}
                         else if ($x == '"') { $quotes = 2; $state = 4; $keyValue = ''; }
                         else if ((($x >='A')&&($x <='Z'))||(($x >='a')&&($x <='z'))) { $state = 4; $keyValue = $x; }
                         break;
                case 4:  if ((($quotes == 1)&&($x == "'"))||(($quotes == 2)&&($x == '"'))) { $quotes = 0; $state=5; }
                         else if (!$quotes &&  (($x == ' ')||($x == chr(9)))) { $state = 5; }
                         else { $keyValue .= $x; }
                         break;
            }

            // Action in case when scanning is complete
            if ($state == 5) {
                $keys [ strtolower($keyName) ] = $keyValue;
                $state = 0;
            }
        }

        // If we finished and we're in stete "scanning value" - register this field
        if ($state == 4) {
            $keys [ strtolower($keyName) ] = $keyValue;
            $state = 0;
        }

        // If we have any other state - report an error
        if ($state) {
            $errorFlag = 1; print "EF ($state)[".$paramLine."].";
        }

        if ($errorFlag) {
            return -1;
        }
        return $keys;
    }

    function bbcodes($content) {
        //global $lang, $config, $userROW;

        //if (!$config['use_bbcodes']) return $content;

        $content    =    preg_replace("#\[code\](.+?)\[/code\]#is", "<pre>$1</pre>",$content);

        $content    =    preg_replace("#\[quote\]\s*(.*?)\s*\[/quote\]#is", "<blockquote>$1</blockquote>",$content);
        $content    =    preg_replace("#\[quote=(.*?)\]\s*(.*?)\s*\[/quote\]#is","<div class='answer'><b>$1</b><br />$2</div>",$content);

        $content    =    preg_replace("#\[acronym\]\s*(.*?)\s*\[/acronym\]#is", "<acronym>$1</acronym>",$content);
        $content    =    preg_replace("#\[acronym=(.*?)\]\s*(.*?)\s*\[/acronym\]#is","<acronym title=\"$1\">$2</acronym>",$content);

        $content    =    preg_replace("#\[email\]\s*(\S+?)\s*\[/email\]#i", "<a href=\"mailto:$1\">$1</a>", $content);
        $content    =    preg_replace("#\[email\s*=\s*\&quot\;([\.\w\-]+\@[\.\w\-]+\.[\.\w\-]+)\s*\&quot\;\s*\](.*?)\[\/email\]#i", "<a href=\"mailto:$1\">$2</a>", $content);
        $content    =    preg_replace("#\[email\s*=\s*([\.\w\-]+\@[\.\w\-]+\.[\w\-]+)\s*\](.*?)\[\/email\]#i", "<a href=\"mailto:$1\">$2</a>", $content);
        $content    =    preg_replace("#\[s\](.*?)\[/s\]#is", "<s>$1</s>", $content);
        $content    =    preg_replace("#\[b\](.+?)\[/b\]#is", "<b>$1</b>", $content);
        $content    =    preg_replace("#\[i\](.+?)\[/i\]#is", "<i>$1</i>", $content);
        $content    =    preg_replace("#\[u\](.+?)\[/u\]#is", "<u>$1</u>", $content);
        $content    =    preg_replace("#\[p\](.+?)\[/p\]#is", "<p>$1</p>", $content);
        $content    =    preg_replace("#\[ul\](.*?)\[/ul\]#is", "<ul>$1</ul>", $content);
        $content    =    preg_replace("#\[li\](.*?)\[/li\]#is", "<li>$1</li>", $content);
        $content    =    preg_replace("#\[ol\](.*?)\[/ol\]#is", "<ol>$1</ol>", $content);
        $content    =    preg_replace("#\[left\](.*?)\[/left\]#is","<p style=\"text-align: left\">$1</p>", $content);
        $content    =    preg_replace("#\[right\](.*?)\[/right\]#is","<p style=\"text-align: right\">$1</p>", $content);
        $content    =    preg_replace("#\[center\](.*?)\[/center\]#is","<p style=\"text-align: center\">$1</p>", $content);

        // Process Images
        // Possible format:
        // '[img' + ( '=' or ' ' ) + URL + flags + ']' + alt + '[/url]'
        // '[img' + flags ']' + url + '[/url]'
        // Allower flags:
        // width
        // height
        // border
        // align: 'left', 'right', 'center'
        // class: anything

        if (preg_match_all("#\[img(\=| *)(.*?)\](.*?)\[\/img\]#is", $content, $pcatch, PREG_SET_ORDER)) {
            $rsrc = array();
            $rdest = array();
            // Scan all IMG tags
            foreach ($pcatch as $catch) {

                // Init variables
                list ($line, $null, $paramLine, $alt) = $catch;
                array_push($rsrc, $line);
                $outkeys = array();

                // Make a parametric line with url
                if (trim($paramLine)) {
                    // Parse params
                    $paramLine = "src=".$paramLine;
                    $keys = $this->parseBBCodeParams($paramLine);
                } else {
                    // No params to scan
                    $keys = array();
                }

                // Return an error if BB code is bad
                if (!is_array($keys)) {
                    array_push($rdest,'[INVALID IMG BB CODE]');
                    continue;
                }

                $keys['alt'] = $alt;
                // Now let's compose a resulting URL
                $outkeys [] = 'src="'.((!$keys['src'])?$alt:$keys['src']).'"';

                // Now parse allowed tags and add it into output line
                foreach ($keys as $kn => $kv) {
                    switch ($kn) {
                        case 'width':
                        case 'height':
                        case 'border':
                                $outkeys[] = $kn.'="'.intval($kv).'"';
                                break;
                        case 'align':
                            if (in_array(strtolower($kv), array( 'left', 'right', 'center')))
                                $outkeys[] = $kn.'="'.strtolower($kv).'"';
                            break;
                        case 'class':
                            $v = str_replace(array(ord(0), ord(9), ord(10), ord(13), ' ', "'", "\"", ";", ":", '<', '>', '&'),'',$kv);
                            $outkeys [] = $kn.'="'.$v.'"';
                            break;
                        case 'alt':
                            $v = str_replace(array("\"", ord(0), ord(9), ord(10), ord(13), ":", '<', '>', '&'),array("'",''),$kv);
                            $outkeys [] = $kn.'="'.$v.'"';
                            break;
                    }
                }
                // Fill an output replacing array
                array_push($rdest, "<img ".(implode(" ", $outkeys)).' />');
            }

/*
            foreach ($pcatch as $catch) {
                $outkeys = array();
                $url = '';

                list ($line, $null, $paramLine, $alt) = $catch;
                $params = preg_split("# +#", $paramLine, -1, PREG_SPLIT_NO_EMPTY);

                if (count($params) && (strpos($params[0], '=') === false)) {
                    $url = array_shift($params);

                    // Cut possible quotes
                    if (preg_match('#^\".+\"$#', $url) || preg_match("#^\'.+\'$#", $url))
                        $url = substr($url, 1, -1);
                }

                foreach ($params as $param) {
                    list ($k, $v) = preg_split('#\=#', $param, 2, PREG_SPLIT_NO_EMPTY);

                    // Cut possible quotes
                    if (preg_match('#^\".+\"$#', $v) || preg_match("#^\'.+\'$#", $v))
                        $v = substr($v, 1, -1);

                    // Process allowed flags
                    switch (strtolower($k)) {
                        case 'width':
                        case 'height':
                        case 'border':
                                $outkeys[] = strtolower($k).'="'.intval($v).'"';
                                break;
                        case 'align':
                            if (in_array(strtolower($v), array( 'left', 'right', 'center')))
                                $outkeys[] = strtolower($k).'="'.strtolower($v).'"';
                            break;
                        case 'class':
                            $v = str_replace(array(ord(0), ord(9), ord(10), ord(13), ' ', "'", "\"", ";", ":", '<', '>', '&'),'',$v);
                            $outkeys[] = strtolower($k).'="'.strtolower($v).'"';
                            break;
                    }
                }

                array_push($rsrc, $line);

                // Strip quotes from URL definition
                $url = trim($url);
                $alt = trim($alt);
                if (!$url) {
                    $url = $alt;
                    if (!(strrpos($alt, '/') === false)) {
                        $alt = substr($alt, strrpos($alt, '/')+1);
                    }
                }

                if (($url = $this->normalize_url($url)) === false) {
                    array_push($rdest,$lang['bb_forbidden']);
                    continue;
                }

                array_push($rdest, "<img src=\"".$url."\" alt=\"".secure_html($alt)."\" ".(implode(" ", $outkeys))." />");
            }
*/
            $content = str_replace($rsrc, $rdest, $content);
        }


        // Process URLS
        // Possible format:
        // '[url' + ( '=' or ' ' ) + URL + flags + ']' + Name + '[/url]'
        // '[url' + flags ']' + url + '[/url]'
        // Allower flags:
        // target: anything
        // class: anythign
        // title: anything

        if (preg_match_all("#\[url(\=| *)(.*?)\](.*?)\[\/url\]#is", $content, $pcatch, PREG_SET_ORDER)) {
            $rsrc = array();
            $rdest = array();
            // Scan all URL tags
            foreach ($pcatch as $catch) {

                // Init variables
                list ($line, $null, $paramLine, $alt) = $catch;
                array_push($rsrc, $line);
                $outkeys = array();

                // Make a parametric line with url
                if (trim($paramLine)) {
                    // Parse params
                    $paramLine = "href=".$paramLine;
                    $keys = $this->parseBBCodeParams($paramLine);
                } else {
                    // No params to scan
                    $keys = array();
                }

                // Return an error if BB code is bad
                if (!is_array($keys)) {
                    array_push($rdest,'[INVALID URL BB CODE]');
                    continue;
                }

                // Now let's compose a resulting URL
                $outkeys [] = 'href="'.((!$keys['href'])?$alt:$keys['href']).'"';

                // Now parse allowed tags and add it into output line
                foreach ($keys as $kn => $kv) {
                    switch ($kn) {
                        case 'class':
                        case 'target':
                            $v = str_replace(array(ord(0), ord(9), ord(10), ord(13), ' ', "'", "\"", ";", ":", '<', '>', '&'),'',$kv);
                            $outkeys [] = $kn.'="'.$v.'"';
                            break;
                        case 'title':
                            $v = str_replace(array("\"", ord(0), ord(9), ord(10), ord(13), ":", '<', '>', '&'),array("'",''),$kv);
                            $outkeys [] = $kn.'="'.$v.'"';
                            break;
                    }
                }
                // Fill an output replacing array
                array_push($rdest, "<a ".(implode(" ", $outkeys)).">".$alt.'</a>');
            }
            $content = str_replace($rsrc, $rdest, $content);
        }

        // Обработка кириллических символов для украинского языка
        $content    =    str_replace(array('[CYR_I]', '[CYR_i]', '[CYR_E]', '[CYR_e]', '[CYR_II]', '[CYR_ii]'), array('&#1030;', '&#1110;', '&#1028;', '&#1108;', '&#1031;', '&#1111;'), $content);

        // Авто-подсветка URL'ов в тексте новости
        $content    =    preg_replace("#(^|\s)((http|https|news|ftp)://\w+[^\s\[\]\<]+)#i", "$1<a href='$2' target='_blank'>$2</a>", $content);

        while (preg_match("#\[color=([^\]]+)\](.+?)\[/color\]#ies", $content)) {
            $content = preg_replace("#\[color=([^\]]+)\](.+?)\[/color\]#ies"  , "\$this->color(array('style'=>'$1','text'=>'$2'))", $content);
        }
        return $content;
    }

    function htmlformatter($content) {
        global $config;



        $content    =    preg_replace('|<br />\s*<br />|', "\n\n", $content);
        $content    =    str_replace(array("\r\n", "\r"), "\n", $content);
        $content    =    preg_replace("/\n\n+/", "\n\n", $content);
        $content    =    preg_replace('/\n/', "<br />", $content);
        $content    =    preg_replace('!<p>\s*(</?(?:table|thead|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|math|p|h[1-6])[^>]*>)\s*</p>!', "$1", $content);
        $content    =    preg_replace("|<p>(<li.+?)</p>|", "$1", $content);
        $content    =    preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $content);
        $content    =    str_replace('</blockquote></p>', '</p></blockquote>', $content);
        $content    =    preg_replace('!<p>\s*(</?(?:table|thead|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|math|p|h[1-6])[^>]*>)!', "$1", $content);
        $content    =    preg_replace('!(</?(?:table|thead|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|math|p|h[1-6])[^>]*>)\s*</p>!', "$1", $content);
        $content    =    preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $content);
        $content    =    preg_replace('!(</?(?:table|thead|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|math|p|h[1-6])[^>]*>)\s*<br />!', "$1", $content);
        $content    =    preg_replace('!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)>)!', '$1', $content);
        $content    =    preg_replace('!(<pre.*-?->)(.*?)</pre>!ise', " stripslashes('$1') .  clean_pre('$2')  . '</pre>' ", $content);
        $content    =    preg_replace("/<code>(.*?)<\/code>/es", "phphighlight('$1')", $content);
        $content    =    str_replace("\n</p>\n", "</p>", $content);

        return $content;
    }

function smilies($content) {
    global $config;

    if (!$config['use_smilies'])
        return $content;

    $smilies_arr    =    explode(",", $config['smilies']);
    foreach ($smilies_arr as $null => $smile) {
        $smile        =    trim($smile);
        $find[]        =    "':$smile:'";
        $replace[]    =    "<img class=\"smilies\" alt=\"$smile\" src=\"".skins_url."/smilies/$smile.gif\" />";
    }
    return preg_replace($find, $replace, $content);
}

function translit($content, $allowDash = 0) {

    $utf2enS = array('А' => 'a', 'Б' => 'b', 'В' => 'v', 'Г' => 'h', 'Ґ' => 'g', 'Д' => 'd', 'Е' => 'e', 'Ё' => 'jo', 'Є' => 'e', 'Ж' => 'zh', 'З' => 'z', 'И' => 'i', 'І' => 'i', 'Й' => 'i', 'Ї' => 'i', 'К' => 'k', 'Л' => 'l', 'М' => 'm', 'Н' => 'n', 'О' => 'o', 'П' => 'p', 'Р' => 'r', 'С' => 's', 'Т' => 't', 'У' => 'u', 'Ў' => 'u', 'Ф' => 'f', 'Х' => 'h', 'Ц' => 'c', 'Ч' => 'ch', 'Ш' => 'sh', 'Щ' => 'sz', 'Ъ' => '', 'Ы' => 'y', 'Ь' => '', 'Э' => 'e', 'Ю' => 'yu', 'Я' => 'ya');
    $utf2enB = array('а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'ґ' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'jo', 'є' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'і' => 'i', 'й' => 'i', 'ї' => 'i', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ў' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sz', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya', '&quot;' => '', '&amp;' => '', 'µ' => 'u', '№' => 'num');

    $content = trim(strip_tags($content));
    $content = strtr($content, $utf2enS);
    $content = strtr($content, $utf2enB);
    $content = preg_replace("/\s+/ms", "_", $content);
    $content = preg_replace("/[ ]+/", "_", $content);

    $cut = ($allowDash)?"/[^a-z0-9_\-\.]+/mi":"/[^a-z0-9_\.]+/mi";
    $content = preg_replace($cut, "", $content);

    return $content;
}

function color($arr) {

    $style = $arr['style'];
    $text  = $arr['text'];
    $style = str_replace('&quot;', '', $style);
    $style = preg_replace("/[&\(\)\.\%\[\]<>\'\"]/", "", preg_replace( "#^(.+?)(?:;|$)#", "$1", $style ));
	$style = preg_replace("/[^\d\w\#\s]/s", "", $style);

	return "<span style=\"color:".$style."\">".$text."</span>";
}
}
?>