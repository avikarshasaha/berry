<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    GNU GPL 2 <http://gnu.org/licenses/gpl-2.0.txt>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
// HTML++ или HTML#, если вы запускаете скрипт на MS Windows.
// Сейчас это вот шутка была.

class Tags extends Attr {
    static $ns;
    static $var = '$GLOBALS';
////////////////////////////////////////////////////////////////////////////////

    function parse($output, $is_final = false){
        $output = self::parse_vars($output);
        $output = self::parse_supadupa($output, $is_final);
        $output = self::sux('<berry>'.$output.'</berry>');

        $values = array();
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parse_into_struct($parser, $output, $values) or self::_errors($parser, $output);
        xml_parser_free($parser);

        $result = self::_values($values, $is_final);
        $result = str_replace(array('<berry>', '</berry>', '<berry />'), '', $result);
        $result = ($is_final ? self::unsux($result) : $result);

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    function parse_else($if, $output){
        return ($if ? $arr[0] : $arr[1]);

////////////////////////////////////////////////////////////////////////////////

    private function _errors($parser, $output){
        $code = xml_get_error_code($parser);

        libxml_use_internal_errors(true);
        simplexml_load_string($output);

        foreach (libxml_get_errors() as $error)
            if ($error->code == $code){
                    $lines[] = $match[1];
                else
                    $lines[] = $error->line;
            }

        sort($lines);

        foreach ($lines as $line)
            $href[] = '<a href="#'.$line.'">'.$line.'</a>';

        echo '<h1>'.xml_error_string($code).'</h1>';
        echo '<h2>Line(s): '.join(', ', $href).'</h2>';
        echo '<table>';

        foreach (explode("\n", self::unsux($output)) as $k => $v){
            $bg = (in_array($i, $lines)  ? 'ffe6e6' : 'f3f3f3');

            echo '<td style="background: #ccc; padding: 5px;">';
            echo '<a name="'.($i + 1).'"></a>'.$i;
            echo '<td style="background: #'.$bg.'; padding: 5px;">';
            echo '<pre>'.self::html($v).'</pre>';
        }

        exit;
    }

////////////////////////////////////////////////////////////////////////////////

    function skip($tag = ''){
        static $skip = array();

        if ($tag)
            $skip[] = strtolower($tag);

        return $skip;
    }

////////////////////////////////////////////////////////////////////////////////

    function parse_vars($output){
    	$output = str_replace('\#', self::char('#'), $output);
        $output = str_replace('\$', self::char('$'), $output);

        if (preg_match_all('/(#|\$|\$\w+){([^}]*)}/U', $output, $match))
            for ($i = 0, $c = count($match[0]); $i < $c; $i++)
                if ($pos = strpos($match[2][$i], '=')){
                    $k = trim(substr($match[2][$i], 0, $pos));
                    $v = trim(substr($match[2][$i], $pos + 1));

                    if (($v[0] == "'" and substr($v, -1) == "'") or ($v[0] == '"' and substr($v, -1) == '"')){
                        $v = substr($v, 1, -1);
                    } elseif ($f = create_function('', 'return '.$v.';')){
                        $v = $f();
                    }

                    if ($match[1][$i] == '#')
                        self::constant($k, $v);
                    elseif ($match[1][$i][0] == '$')
                        self::vars($k, $v);

                    $output = str_replace($match[0][$i], '', $output);
                } else {
                    if ($match[1][$i] == '#'){
                        $output = str_replace($match[0][$i], self::constant($match[2][$i]), $output);
                        continue;
                    }

                    $var = self::vars($match[2][$i]);
                    $match[1][$i] = strtolower($match[1][$i]);

                    if (($type = substr($match[1][$i], 1)) and function_exists($func = 'type_'.$type))
                        $result = call_user_func($func, $match[2][$i], $var);
                    else
                        $result = (is_array($var) ? self::serialize($var) : $var);

                    $output = str_replace($match[0][$i], $result, $output);
                }

        return $output;
    }

////////////////////////////////////////////////////////////////////////////////

    function parse_lvars($output, $key, $value, $each = false){

        $output = str_replace('\%', self::char('%'), $output);

        $rand    = rand();
        $pattern = '/%(\w+)?{'.preg_quote($key, '/').'(.([^}]*))?}/U';
        $replace = 'function.tags.parse_lvars.'.$key.'.'.$rand;

        if ($each){

            foreach ($keys as $k)
                $result .= preg_replace($pattern, '$\\1{'.$replace.'.'.$k.'\\2}', $output);

            $value[$keys[0]]['#is_first'] = true;
            $value[$k]['#is_last'] = true;
        } else {
            $result = preg_replace($pattern, '$\\1{'.$replace.'\\2}', $output);
        }

        self::vars($replace, $value);
        $result = self::parse($result);
        unset($GLOBALS['function']['tags']['parse_lvars'][$key][$rand]);
        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    private function _values($values, $is_final = false){

        foreach ($values as $it){
            $it['attributes']['#is_final'] = $is_final;
            $it['attributes']['#tag'] = self::ns($it['tag']);
            $it['attributes'] = array_change_key_case($it['attributes']);
            $it['attr'] = ($skip['tag'] ? $it['attributes'] : attr::normalize($it['attributes']));

            if (self::$ns){

                    !function_exists($func = 'tag_'.$it['attr']['#tag']) or
                    substr($ns, 0, (strlen(self::$ns) + 1)) != self::$ns.'_'
                )
                    unset($func);
            } elseif (!function_exists($func = 'tag_'.$it['attr']['#tag'])){
                unset($func);
            }

            if ($it['attr']['#skip'] and $it['attr']['#skip'] !== true)
                $result .= $it['attr']['#skip'];

            if ($skip['attr'] and $skip['attr']['level'] >= $it['level'] and ($it['type'] == 'open' or $it['type'] == 'complete'))
                unset($skip['attr']);

            if (!$skip['attr'] and $it['attr']['#skip'])
                $skip['attr'] = $it;

            if ($skip['attr'] and $skip['attr']['level'] <= $it['level'])
                continue;

            if ($skip['tag'] and $skip['tag']['level'] == $it['level'] and ($it['type'] == 'close' or $it['type'] == 'complete'))
                unset($skip['tag']);

            if (!$skip['tag'] and in_array($it['attr']['#tag'], $skipit) and $it['type'] == 'open')
                $skip['tag'] = $it;

            $result .= $it['attr']['#before'];

            if ((!$skip['tag'] or $skip['tag']['level'] >= $it['level']) and $func and ($it['type'] == 'open' or $it['type'] == 'complete')){
                $tmp[$it['tag']][$it['level']]['result'] = $result;
                $tmp[$it['tag']][$it['level']]['attr']   = $it['attr'];
                unset($result);
            }

            if (!$tmp[$it['tag']][$it['level']]){
                unset($attr);
                foreach ($it['attr'] as $k => $v)
                    if ($k[0] != '#'){
                        $quote = (is_int(strpos($v, '"')) ? "'" : '"');
                        $attr .= ' '.$k.'='.$quote.$v.$quote;
                    }

                if ($it['type'] == 'open' or ($it['type'] == 'complete' and ($it['value'] or self::is_container($it['attr']['#tag']))))
                    $result .= '<'.$it['tag'].$attr.'>';
                elseif ($it['type'] == 'complete' and !$it['value'])
                    $result .= '<'.$it['tag'].$attr.' />';

                $result .= $it['value'];

                if ($it['type'] == 'close' or ($it['type'] == 'complete' and ($it['value'] or self::is_container($it['attr']['#tag']))))
                    $result .= '</'.$it['tag'].'>';
            } else {
                $result .= $it['value'];
            }

            if ($tmp[$it['tag']][$it['level']] and ($it['type'] == 'close' or $it['type'] == 'complete')){
                if ($result)
                    $tmp[$it['tag']][$it['level']]['attr']['#text'] = $result;

                foreach ($tmp[$it['tag']][$it['level']]['attr'] as $k => $v)
                    $tmp[$it['tag']][$it['level']]['attr'][$k] = self::unsux($v);

                $result  = $tmp[$it['tag']][$it['level']]['result'];
                $result .= call_user_func($func, attr::normalize($tmp[$it['tag']][$it['level']]['attr'], false));
                unset($tmp[$it['tag']][$it['level']]);
            }

            $result .= $it['attr']['#after'];
        }

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    function fill($tag, $attr = array()){
        if (is_array($tag)){
            $attr = $tag;
            $tag  = $attr['#tag'];
        }

        $cont = (array_key_exists('#text', $attr) or self::is_container($tag));

        foreach ($attr as $k => $v)
            if (is_array($v))
                foreach ($v as $k2 => $v2){
                    $attr[$k.'_'.$k2] = $v2;
                    unset($attr[$k]);
                }

        foreach (attr::normalize($attr) as $k => $v){
            if ($k[0] != '#'){
                $quote  = (is_int(strpos($v, '"')) ? "'" : '"');
                $attrs .= ' '.$k.'='.$quote.$v.$quote;
            }
        }

        $result = $attr['#before'].'<'.$tag.$attrs.(!$cont ? ' /' : '').'>';

        if ($cont)
            $result .= $attr['#text'].'</'.$tag.'>';

        $result .= $attr['#after'];
        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    function sux($output){
        $output = str_replace('&', self::char('&'), $output);
        $output = preg_replace('/\<([^a-z^\/])/ie', "str_replace('<', self::char('<'), '<\\1')", $output);

        return $output;
    }

////////////////////////////////////////////////////////////////////////////////

    function unsux($output){
        $output = str_replace(self::char('&'), '&', $output);
        $output = str_replace(self::char('$'), '$', $output);
        $output = str_replace(self::char('#'), '#', $output);
        $output = str_replace(self::char('@'), '@', $output);
        $output = str_replace(self::char('%'), '%', $output);
        $output = str_replace(self::char('<'), '<', $output);

        return $output;
    }

////////////////////////////////////////////////////////////////////////////////

    function unhtml($string, $quote_style = ENT_QUOTES){

    	return str_replace('&#96;', '`', $string);
    }

////////////////////////////////////////////////////////////////////////////////

    function html($string, $quote_style = ENT_QUOTES){

    	return str_replace('`', '&#96;', $string);
    }

////////////////////////////////////////////////////////////////////////////////

    function char($char){
        return '%it['.ord($char).']';
    }

////////////////////////////////////////////////////////////////////////////////

    function varname($string, $ns = ''){

        if (!$ns)
            $ns = self::$var;

        if (isset($array[$ns][$string]))
            return $array[$ns][$string];

        $var  = $string;
        $vars = array(
            '_get'     => '_GET',
            '_post'    => '_POST',
            '_files'   => '_FILES',
            '_session' => '_SESSION',
            '_cookie'  => '_COOKIE',
            '_server'  => '_SERVER',
        );

        if (substr($var, 0, 8) == '_cookie.')
            $var = '_cookie.'.str_replace('.', '_', substr($var, 8));

        $var = str_replace('\.', self::char('.'), $var);
        $var = explode('.', $var);

        if ($vars[$var[0]])
            $var[0] = $vars[$var[0]];

        $var = $ns.($var ? "['".join("']['", $var)."']" : '');
        $var = str_replace("['']", '[]', $var);
        $var = str_replace(self::char('.'), '.', $var);

        return $array[$ns][$string] = $var;
    }

////////////////////////////////////////////////////////////////////////////////

    function vars(){
        $var  = self::varname($args[0]);
        $name = explode('.', str_replace('\.', self::char('.'), $args[0]), 2);
        $map  = array(
            '&' => '_ampersand_',
            '@' => '_at_',
            '*' => '_asterisk_',
            '^' => '_caret_',
            '#'	=> '_octothorp_',
            '$' => '_dollar_',
            '%' => '_percent_',
            '/' => '_slash_',
            '\\' => '_backslash_',
            self::char('.') => '_dot_'
        );

        if (function_exists($func = 'var_'.strtr($name[0], $map))){
            return call_user_func_array($func, $args);
        } elseif (func_num_args() == 2){
            if ($func = create_function('$def', 'return '.$var.' = $def;'))
                return $func($args[1]);
        } else {
            if ($func = create_function('', 'if (isset('.$var.')) return '.$var.';'))
                return $func();
        }
    }

////////////////////////////////////////////////////////////////////////////////

    function constant(){
        $args = func_get_args();

        if (func_num_args() == 2){
            if (!defined($args[0])){
                define($args[0], $args[1]);
                return constant($args[0]);
            }
        } else {
            if (defined($args[0]))
                return constant($args[0]);
            elseif (defined($args[0] = str_replace('.', '_', $args[0])))
                return constant($args[0]);
        }
    }

////////////////////////////////////////////////////////////////////////////////

    function elmname_parse($string){
    	    return $string;

        $char = base64_encode(self::char('.'));

        $string = str_replace('.', $char, $string);
        $string = str_replace('][', '.', $string);
        $string = str_replace('[', '.', $string);
        $string = str_replace(']', '', $string);
        $string = str_replace($char, '\.', $string);

        return $string;
    }

////////////////////////////////////////////////////////////////////////////////

    function elmname_unparse($array){
        if (!is_array($array)){
            $array = str_replace('\.', self::char('.'), $array);
            $array = explode('.', $array);
        }

        $parent = array_shift($array);
        $string = $parent.($array ? '['.join('][', $array).']' : '');
        return str_replace(self::char('.'), '.', $string);
    }

////////////////////////////////////////////////////////////////////////////////

    function serialize($value){
        return base64_encode(serialize($value));
    }

////////////////////////////////////////////////////////////////////////////////

    function unserialize($value){
        $result = unserialize(base64_decode($value));
        return ($result ? $result : array());
    }

////////////////////////////////////////////////////////////////////////////////

    function is_array($value){
        return is_array(unserialize(base64_decode($value)));
    }

////////////////////////////////////////////////////////////////////////////////

    function is_container($tag = ''){
        static $tags;

        if (!$tags)
            $tags = array_flip(array(
                'a',
                'abbr',
                'acronym',
                'address',
                'applet',
                'b',
                'bdo',
                'bgsound',
                'big',
                'blink',
                'blockquote',
                'body',
                'button',
                'caption',
                'center',
                'cite',
                'code',
                'colgroup',
                'comment',
                'dd',
                'del',
                'dfn',
                'dir',
                'div',
                'dl',
                'dt',
                'em',
                'fieldset',
                'font',
                'form',
                'frameset',
                'h1',
                'h2',
                'h3',
                'h4',
                'head',
                'html',
                'i',
                'iframe',
                'ilayer',
                'ins',
                'kbd',
                'label',
                'layer',
                'legend',
                //'li',
                'listing',
                'map',
                'marquee',
                'menu',
                'multicol',
                'nobr',
                'noembed',
                'noframes',
                'nolayer',
                'noscript',
                'object',
                'ol',
                'optgroup',
                'option',
                //'p',
                'plaintext',
                'pre',
                'q',
                'rb',
                'rbc',
                'rp',
                'rt',
                'ruby',
                's',
                'samp',
                'script',
                'select',
                'small',
                'span',
                'strike',
                'strong',
                'style',
                'sub',
                'sup',
                'table',
                'tbody',
                //'td',
                'textarea',
                'tfoot',
                //'th',
                'thead',
                'title',
                //'tr',
                'tt',
                'u',
                'ul',
                'var',
                'xml',
                'xmp',
                'noindex',
                'berry'
            ));

        return ($tag ? is_int($tags[$tag]) : $tags);
    }

////////////////////////////////////////////////////////////////////////////////

    function parse_supadupa($output, $is_final = false){

        if (!$tags){
            $tags = str_replace('_', '[_\.:-]', $ns.join('|'.$ns, array_keys(self::functions('supadupa'))));
        }

        if (preg_match_all('/<('.$tags.')( ([^>]*))?>(.*?)<\/\\1>/isU', $output, $match))
            for ($i = 0, $c = count($match[0]); $i < $c; $i++){

                if (preg_match_all('/ ([\w\.:-]+)=("|\')(.*?)\\2/is', $match[2][$i], $match2)){
                    for ($i2 = 0, $c2 = count($match2[0]); $i2 < $c2; $i2++)
                        $match2[1][$i2] = strtolower(str_replace(array(':', '-', '.'), '_', $match2[1][$i2]));

                    $attr = array_merge($attr, array_combine($match2[1], $match2[3]));
                    $attr = attr::normalize($attr);
                }

                if ($attr['#skip']){
                    if ($attr['#skip'] !== true)
                        $output = str_replace($match[0][$i], $attr['#skip'], $output);
                } else {
                    $output = str_replace($match[0][$i], call_user_func('supadupa_'.$attr['#tag'], $attr), $output);
                }
            }

        return $output;
    }

////////////////////////////////////////////////////////////////////////////////

    function functions($prefix = ''){
    	if (!$prefix)
    	    return end(get_defined_functions());

        $funcs = end(get_defined_functions());
    	$len = (strlen($prefix) + 1);

        foreach ($funcs as $func)
            if (substr($func, 0, $len) == $prefix.'_')
                if ($func = substr($func, $len))
                    $result[$func] = $prefix.'_'.$func;

        return ($result ? $result : array());
    }

////////////////////////////////////////////////////////////////////////////////

    function constants($prefix = ''){
    	if (!$prefix)
    	    return array_keys(get_defined_constants());

        $consts = array_keys(get_defined_constants());
    	$len = (strlen($prefix) + 1);

        foreach ($consts as $const)
            if (substr($const, 0, $len) == $prefix.'_')
                if ($const = substr($const, $len))
                    $result[$const] = $prefix.'_'.$const;

        return ($result ? $result : array());
    }

////////////////////////////////////////////////////////////////////////////////

    function ns($tag){

        if (!self::$ns)
            return $tag;

        $ns = (strlen(self::$ns) + 1);

        if (substr($tag, 0, $ns) == self::$ns.'_')
            return substr($tag, $ns);

        return $tag;
    }

////////////////////////////////////////////////////////////////////////////////

}