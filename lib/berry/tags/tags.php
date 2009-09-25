<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
// HTML++ или HTML#, если вы запускаете скрипт на MS Windows.
// Сейчас это вот шутка была.

class Tags extends Attr {
    protected static $ns;
    protected static $var;
    protected static $cache = array();
////////////////////////////////////////////////////////////////////////////////

    static function parse($output, $is_final = false){        self::$ns = b::config('lib.tags.ns');
        self::$var = b::config('lib.tags.var');

        $output = self::_vars($output);
        $output = self::_supadupa($output, $is_final);
        $output = self::_sux('<berry>'.$output.'</berry>');

        $values = array();
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);

        if (!xml_parse_into_struct($parser, $output, $values))
            throw new exception(self::_trace($parser, $output));

        xml_parser_free($parser);

        $result = self::_values($values, $is_final);
        $result = str_replace(array('<berry>', '</berry>', '<berry />'), '', $result);
        $result = ($is_final ? self::_unsux($result) : $result);

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    static function parse_else($if, $output){        $arr = preg_split('/<else( ([^>]*))?\/>/i', $output, 2);
        return ($if ? $arr[0] : $arr[1]);    }

////////////////////////////////////////////////////////////////////////////////

    static function parse_vars($output, $key, $value = null, $each = false){        if (is_array($output))            list($output, $key, $value, $each) = array($output['#text'], $output['#tag'], $key, $value);

        $output = str_replace('\%', self::char('%'), $output);

        $rand    = rand();
        $pattern = '/%(\w+)?{'.preg_quote($key, '/').'(.([^}]*))?}/U';
        $replace = 'lib.tags.parse_vars.'.$key.'.'.$rand;

        if ($each){            $keys = array_keys($value);

            foreach ($keys as $k)
                $result .= preg_replace($pattern, '$\\1{'.$replace.'.'.$k.'\\2}', $output);

            $value[$keys[0]]['#is_first'] = true;
            $value[$k]['#is_last'] = true;
        } else {
            $result = preg_replace($pattern, '$\\1{'.$replace.'\\2}', $output);
        }

        self::variable($replace, $value);
        $result = self::parse($result);
        //unset($GLOBALS['lib']['tags']['parse_vars'][$key][$rand]);
        self::variable($replace, null);
        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    static function fill($tag, $attr = array()){
        if (is_array($tag)){
            $attr = $tag;
            $tag  = $attr['#tag'];
        }

        $cont = (array_key_exists('#text', $attr) or self::has_end_tag($tag));

        foreach ($attr as $k => $v)
            if (is_array($v))
                foreach ($v as $k2 => $v2){
                    $attr[$k.'_'.$k2] = $v2;
                    unset($attr[$k]);
                }

        foreach (parent::normalize($attr) as $k => $v){
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

    static function unhtml($string){
        $string = str::unhtml($string, $quote_style);
        $string = preg_replace(
            array('/#(\w+)?{/', '/\$(\w+)?{/', '/%(\w+)?{/', '/@(\w+)?{/'),
            array('&#35;\\1{', '&#36;\\1{', '&#37;\\1{', '&#64;\\1{'),
        $string);

    	return str_replace('`', '&#96;', $string);
    }

////////////////////////////////////////////////////////////////////////////////

    static function html($string){        $string = str::html($string);        $string = preg_replace(
            array('/&#35;(\w+)?{/', '/&#36;(\w+)?{/', '/&#37;(\w+)?{/', '/&#64;(\w+)?{/'),
            array('#\\1{', '$\\1{', '%\\1{', '@\\1{'),
        $string);

    	return str_replace('&#96;', '`', $string);
    }

////////////////////////////////////////////////////////////////////////////////

    static function varname($string, $ns = ''){        if (!$ns)
            $ns = b::config('lib.tags.var');

        if (isset(self::$cache['varname'][$ns][$string]))
            return self::$cache['varname'][$ns][$string];

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

        return self::$cache['varname'][$ns][$string] = $var;
    }

////////////////////////////////////////////////////////////////////////////////

    static function variable(){        $args = func_get_args();
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

        if (b::function_exists($func = 'var_'.strtr($name[0], $map))){            $args[0] = substr($args[0], (strlen($name[0]) + 1));
            return b::call('*'.$func, $args);
        } elseif (func_num_args() == 2){
            if ($func = create_function('$def', 'return '.$var.' = $def;'))
                return $func($args[1]);
        } else {
            if ($func = create_function('', 'if (isset('.$var.')) return '.$var.';'))
                return $func();
        }
    }

////////////////////////////////////////////////////////////////////////////////

    static function constant(){
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

    static function elmname_parse($string){    	if (!strpos($string, '['))
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

    static function elmname_unparse($array){
        if (!is_array($array)){
            $array = str_replace('\.', self::char('.'), $array);
            $array = explode('.', $array);
        }

        $parent = array_shift($array);
        $string = $parent.($array ? '['.join('][', $array).']' : '');
        return str_replace(self::char('.'), '.', $string);
    }

////////////////////////////////////////////////////////////////////////////////

    static function serialize($value){
        return base64_encode(serialize($value));
    }

////////////////////////////////////////////////////////////////////////////////

    static function unserialize($value){
        $result = unserialize(base64_decode($value));
        return ($result ? $result : array());
    }

////////////////////////////////////////////////////////////////////////////////

    static function is_array($value){
        return is_array(unserialize(base64_decode($value)));
    }

////////////////////////////////////////////////////////////////////////////////

    static function has_close_tag($tag){
        if (!isset(self::$cache['has_close_tag']))
            self::$cache['has_close_tag'] = array_flip(b::config('lib.tags.has_close_tag'));

        return isset(self::$cache['has_close_tag'][$tag]);
    }

////////////////////////////////////////////////////////////////////////////////

    static function functions($prefix = ''){    	if (!isset(self::$cache['functions'])){
            !cache::exists('ext.php') and b::call('#');

            self::$cache['functions'] = array_merge(
                end(get_defined_functions()),
                array_keys(include cache::exists('ext.php'))
            );
    	}

    	if (!$prefix)
    	    return self::$cache['functions'];

    	 $len = (b::len($prefix) + 1);

        foreach (self::$cache['functions'] as $func)
            if (substr($func, 0, $len) == $prefix.'_')
                if ($func = substr($func, $len))
                    $result[$func] = $prefix.'_'.$func;

        return ($result ? $result : array());
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function _ns($tag){        $tag = strtolower(str_replace(array(':', '-', '.'), '_', $tag));

        if (!self::$ns)
            return $tag;

        $ns = (strlen(self::$ns) + 1);

        if (substr($tag, 0, $ns) == self::$ns.'_')
            return substr($tag, $ns);

        return $tag;
    }


////////////////////////////////////////////////////////////////////////////////

    static function char($char){
        return '%it['.ord($char).']';
    }

////////////////////////////////////////////////////////////////////////////////

protected static function _sux($output){
        $output = str_replace('&', self::char('&'), $output);
        $output = preg_replace('/\<([^a-z^\/])/ie', "str_replace('<', self::char('<'), '<\\1')", $output);

        return $output;
    }

////////////////////////////////////////////////////////////////////////////////

    // Используется Attr
    static function _unsux($output){
        $output = str_replace(self::char('&'), '&', $output);
        $output = str_replace(self::char('$'), '$', $output);
        $output = str_replace(self::char('#'), '#', $output);
        $output = str_replace(self::char('@'), '@', $output);
        $output = str_replace(self::char('%'), '%', $output);
        $output = str_replace(self::char('<'), '<', $output);

        return $output;
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function _trace($parser, $output){
        $lines = array();
        $code = xml_get_error_code($parser);

        libxml_use_internal_errors(true);
        simplexml_load_string($output);

        foreach (libxml_get_errors() as $error)
            if ($error->code == $code){
                if (preg_match('/line (\d+)/', $error->message, $match))
                    $lines[] = $match[1];
                else
                    $lines[] = $error->line;
            }

        sort($lines);

        foreach ($lines as $line)
            $href[] = '<a href="#'.$line.'">'.$line.'</a>';

        $result = '<h1>'.xml_error_string($code).'</h1>';

        if ($lines)
            $result .= '<h2>Line'.(b::len($lines) > 1 ? 's' : '').': '.join(', ', $href).'</h2>';

        $result .= '<table>';

        foreach (explode("\n", self::_unsux($output)) as $k => $v){
            $i  = ($k + 1);
            $bg = (in_array($i, $lines)  ? 'ffe6e6' : 'f3f3f3');

            $result .= '<tr>';
            $result .= '<td style="background: #ccc; padding: 5px;">';
            $result .= '<a name="'.($i + 1).'"></a>'.$i;
            $result .= '<td style="background: #'.$bg.'; padding: 5px;">';
            $result .= '<pre>'.self::unhtml($v).'</pre>';
        }

        $result .= '</table>';

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function _vars($output){
        $output = str_replace('\#', self::char('#'), $output);
        $output = str_replace('\$', self::char('$'), $output);

        if (preg_match_all('/(#|\$|\$\w+){([^}]*)}/U', $output, $match))
            for ($i = 0, $c = count($match[0]); $i < $c; $i++)
                if ($pos = strpos($match[2][$i], '=')){
                    $k = trim(substr($match[2][$i], 0, $pos));
                    $v = trim(substr($match[2][$i], $pos + 1));

                    if (($v[0] == "'" and substr($v, -1) == "'") or ($v[0] == '"' and substr($v, -1) == '"'))
                        $v = substr($v, 1, -1);
                    elseif ($f = create_function('', 'return '.$v.';'))
                        $v = $f();

                    if ($match[1][$i] == '#')
                        self::constant($k, $v);
                    elseif ($match[1][$i][0] == '$')
                        self::variable($k, $v);

                    $output = str_replace($match[0][$i], '', $output);
                } else {
                    if ($match[1][$i] == '#'){
                        $output = str_replace($match[0][$i], self::constant($match[2][$i]), $output);
                        continue;
                    }

                    $var = self::variable($match[2][$i]);
                    $match[1][$i] = strtolower($match[1][$i]);

                    if (($type = substr($match[1][$i], 1)) and b::function_exists($func = 'type_'.$type))
                        $result = b::call($func, $match[2][$i], $var);
                    else
                        $result = (is_array($var) ? self::serialize($var) : $var);

                    $output = str_replace($match[0][$i], $result, $output);
                }

        return $output;
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function _values($values, $is_final = false){
        foreach ($values as $it){
            $it['attributes']['#is_final'] = $is_final;
            $it['attributes']['#tag'] = self::_ns($it['tag']);
            $it['attributes'] = array_change_key_case($it['attributes']);
            $it['attr'] = ($skip['tag'] ? $it['attributes'] : parent::normalize($it['attributes']));
            $exists = (
                b::function_exists($func = 'tag_'.$it['attr']['#tag']) or
                b::function_exists($func = 'container_'.$it['attr']['#tag'])
            );

            if (self::$ns){
                $ns = str_replace(array(':', '-', '.'), '_', $it['tag']);

                if (!$exists or substr($ns, 0, (strlen(self::$ns) + 1)) != self::$ns.'_')
                    unset($func);
            } elseif (!$exists){
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

            if (!$skip['tag'] and substr($func, 0, 9) == 'container' and $it['type'] == 'open')
                $skip['tag'] = $it;

            $result .= $it['attr']['#before'];

            if ((!$skip['tag'] or $skip['tag']['level'] >= $it['level']) and $func and ($it['type'] == 'open' or $it['type'] == 'complete')){
                $tmp[$it['tag']][$it['level']]['result'] = $result;
                $tmp[$it['tag']][$it['level']]['attr']   = $it['attr'];
                unset($result);
            }

            if (!$tmp[$it['tag']][$it['level']]){
                $attr = '';
                $has_end_tag = ($it['value'] or self::has_close_tag($it['attr']['#tag']));

                foreach ($it['attr'] as $k => $v)
                    if ($k[0] != '#'){
                        $quote = (is_int(strpos($v, '"')) ? "'" : '"');
                        $attr .= ' '.$k.'='.$quote.$v.$quote;
                    }

                if ($it['type'] == 'open' or ($it['type'] == 'complete' and $has_end_tag))
                    $result .= '<'.$it['tag'].$attr.'>';
                elseif ($it['type'] == 'complete' and !$it['value'])
                    $result .= '<'.$it['tag'].$attr.' />';

                $result .= $it['value'];

                if ($it['type'] == 'close' or ($it['type'] == 'complete' and $has_end_tag))
                    $result .= '</'.$it['tag'].'>';
            } else {
                $result .= $it['value'];
            }

            if ($tmp[$it['tag']][$it['level']] and ($it['type'] == 'close' or $it['type'] == 'complete')){
                if ($result)
                    $tmp[$it['tag']][$it['level']]['attr']['#text'] = $result;

                foreach ($tmp[$it['tag']][$it['level']]['attr'] as $k => $v)
                    $tmp[$it['tag']][$it['level']]['attr'][$k] = self::_unsux($v);

                $result  = $tmp[$it['tag']][$it['level']]['result'];
                $result .= b::call($func, parent::normalize($tmp[$it['tag']][$it['level']]['attr'], false));

                unset($tmp[$it['tag']][$it['level']]);
            }

            $result .= $it['attr']['#after'];
        }

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function _supadupa($output, $is_final = false){
        if (!isset(self::$cache['_supadupa'])){
            $ns = (self::$ns ? self::$ns.'_' : '');
            self::$cache['_supadupa'] = str_replace('_', '[_\.:-]', $ns.join('|'.$ns, array_keys(self::functions('supadupa'))));
        }

        if (preg_match_all('/<('.self::$cache['_supadupa'].')( ([^>]*))?>(.*?)<\/\\1>/isU', $output, $match))
            for ($i = 0, $c = count($match[0]); $i < $c; $i++){
            	$attr = array('#tag' => self::_ns($match[1][$i]), '#is_final' => $is_final);
            	$match[2][$i] = self::_vars($match[2][$i]);

                if (preg_match_all('/ ([\w\.:-]+)=("|\')(.*?)\\2/is', $match[2][$i], $match2)){
                    for ($i2 = 0, $c2 = count($match2[0]); $i2 < $c2; $i2++)
                        $match2[1][$i2] = strtolower(str_replace(array(':', '-', '.'), '_', $match2[1][$i2]));

                    $attr = array_merge($attr, array_combine($match2[1], $match2[3]));
                    $attr = parent::normalize($attr);
                }

                if ($attr['#skip']){
                    if ($attr['#skip'] !== true)
                        $output = str_replace($match[0][$i], $attr['#skip'], $output);
                } else {
                	$attr['#text'] = $match[4][$i];
                    $output = str_replace($match[0][$i], b::call('supadupa_'.$attr['#tag'], $attr), $output);
                }
            }

        return $output;
    }

////////////////////////////////////////////////////////////////////////////////

}