<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Piles {    protected static $cache = array();
////////////////////////////////////////////////////////////////////////////////

    static function show($output = '', $_ = array()){
        $name = ($output ? $output : b::config('lib.b.show'));
        $name = str_replace('.', '/', $name);
        $files = array('ext/'.$name, 'mod/'.$name, 'lib/berry/'.$name, 'lib/'.$name);

        foreach ($files as $file)
            if (
                is_file(self::$cache['show'] = file::path($file.'.phtml')) or
                is_file(self::$cache['show'] = file::path($file.'/index.phtml'))
            ){                $file = self::$cache['show'];
                if (!self::$cache['show'] = cache::get_path('piles/'.$name.'.php', compact('file')))                    self::$cache['show'] = cache::set('<?php '.self::parse(file_get_contents($file)));

                unset($output, $name, $files, $file);
                extract($_);

                ob_start();
                    include self::$cache['show'];
                return ob_get_clean();
            }

        self::$cache['show'] = $output;
        unset($output, $string, $file);
        extract($_);

        ob_start();
            $eval = eval(self::parse(self::$cache['show']));
        $result = ob_get_clean();

        if ($eval === false)
            throw new Piles_Except($result, trim(self::$cache['show']));

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    static function char($char){
        return '%it['.ord($char).']';
    }

////////////////////////////////////////////////////////////////////////////////

    static function varname($string, $ns = ''){        if (isset(self::$cache['varname'][$ns][$string]))
            return self::$cache['varname'][$ns][$string];
        $var  = $string;
        $vars = array(
            '_global'  => 'GLOBALS',
            '_get'     => '_GET',
            '_post'    => '_POST',
            '_files'   => '_FILES',
            '_session' => '_SESSION',
            '_cookie'  => '_COOKIE',
            '_server'  => '_SERVER'
        );

        if (substr($var, 0, 8) == '_cookie.')
            $var = '_cookie.'.str_replace('.', '_', substr($var, 8));

        $var = str_replace('\.', self::char('.'), $var);
        $var = explode('.', $var);

        if ($vars[$var[0]]){
            $ns = '$'.$vars[$var[0]];
            unset($var[0]);
        } elseif (!$ns){            $ns = '$'.$var[0];
            unset($var[0]);        }

        $var = $ns.($var ? "['".join("']['", $var)."']" : '');
        $var = str_replace("['']", '[]', $var);
        $var = str_replace(self::char('.'), '.', $var);

        return self::$cache['varname'][$ns][$string] = $var;
    }

////////////////////////////////////////////////////////////////////////////////

    static function name2var($string){
    	if (!strpos($string, '['))
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

    static function var2name($array){
        if (!is_array($array)){
            $array = str_replace('\.', self::char('.'), $array);
            $array = explode('.', $array);
        }

        $parent = array_shift($array);
        $string = $parent.($array ? '['.join('][', $array).']' : '');
        return str_replace(self::char('.'), '.', $string);
    }

////////////////////////////////////////////////////////////////////////////////

    static function fill($tag, $attr = array()){
        if (is_array($tag))            list($tag, $attr) = array($tag['#tag'], $tag);

        foreach ($attr as $k => $v)
            if (is_array($v))
                foreach ($v as $k2 => $v2){
                    $attr[$k.'_'.$k2] = $v2;
                    unset($attr[$k]);
                }

        foreach ($attr as $k => $v)
            if ($k[0] != '#'){
                $quote  = (is_int(strpos($v, '"')) ? "'" : '"');
                $attrs .= ' '.str_replace(array(':', '-', '.'), '_', $k).'='.$quote.$v.$quote;
            }

        $cont = array_key_exists('#text', $attr);
        $result  = $attr['#before'].'<'.$tag.$attrs.(!$cont ? ' /' : '').'>';
        $result .= ($cont ? $attr['#text'].'</'.$tag.'>' : '');
        $result .= $attr['#after'];

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    static function call($attr = array()){        if ($attr['#skip'])
            return ($attr['#skip'] === true ? '' : $attr['#skip']);
        if (!b::function_exists($func = 'tag_'.$attr['#tag']))
            return self::fill($attr);

        return $attr['#before'].b::call($func, $attr).$attr['#after'];    }

////////////////////////////////////////////////////////////////////////////////

    static function attr_group($group, $attr){
        if (is_array($attr[$group]))
            return $attr[$group];

        $result = array();

        foreach ($attr as $k => $v)
            if (substr($k, 0, (strlen($group) + 1)) == $group.'_')
                $result[substr($k, (strlen($group) + 1))] = $v;

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function _var($name){        $var = explode('{', str_replace("'", "\'", $name), 2);
        $var[0] = substr($var[0], 1);
        $var[2] = self::varname($var[1]);

        $func = reset(explode('.', str_replace('\.', self::char('.'), $var[1])));
        $func = strtr($func, array(
            self::char('#') => '_octothorp_',

            '*' => '_asterisk_',
            '/' => '_slash_',
            '+' => '_plus_',
            '-' => '_minus_',

            '$' => '_dollar_',
            '%' => '_percent_',
            '@' => '_at_',

            '&' => '_ampersand_',
            '~' => '_tilde_',
            '^' => '_caret_'
        ));

        if (b::function_exists($func = 'var_'.$func)){
            $tmp = explode('.', $var[1]);
            unset($tmp[0]);

            $var[2] = 'b::call(`'.$func.'`, `'.str_replace("'", "\'", join('.', $tmp)).'`, get_defined_vars())';
        }

        if ($var[0] and b::function_exists($func = 'type_'.$var[0]))
            $var[2] = 'b::call(`'.$func.'`, '.$var[2].')';

        return $var[2];
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function _vars($output){        if (preg_match_all('/(\$|\$\w+){([^}]*)}/sU', $output, $match))
            for ($i = 0, $c = b::len($match[0]); $i < $c; $i++)
                $output = str_replace($match[0][$i], self::_var($match[1][$i].'{'.$match[2][$i]), $output);

        return $output;
    }

////////////////////////////////////////////////////////////////////////////////

    static function tokenize($output){
        $sux = array(
            '/*' => '\/*',
            '//' => '\//',
            '#'  => '\#',

            '<? ' => '<?php ',
            '<?=' => '<?php echo ',
            '?>'  => '<?',
            '`'   => '\`' ,
            "'"   => "\'",

            '<script>'  => '\<script>',
            '<script '  => '\<script ',
            '</script>' => '\</script>'
        );

        $output = str_ireplace(array_keys($sux), array_values($sux), $output);
        $output = preg_replace('/\\\\(\S)/e', "self::char('\\1')", trim($output));
        $token = @token_get_all('<?php '.$output);
        $tags = $scope = array();
        $opened = false;
        $mask = '%s:%d';

        for ($i = 1, $c = b::len($token); $i < $c; $i++){            $is_var = ($token[$i] == '$' or (is_array($token[$i]) and $token[$i][1][0] == '$'));
            if (
                $is_var and is_array($token[$i + 1]) and
                $token[$i + 1][1][0] == '{' and ($pos = strpos($token[$i + 1][1], '}'))
            ){                $var = (is_array($token[$i]) ? $token[$i][1] : $token[$i]);
                $var = self::_var($var.substr($token[$i + 1][1], 0, $pos));
                $tags[sprintf($mask, '$', $i)] = $var;
                $tags[] = substr($token[$i + 1][1], ($pos + 1));
                $skip = 1;
            } elseif ($is_var and $token[$i + 1] == '{' and array_search('}', $token)){
                $tmp = 0;
                $var = '';

                for ($j = $i; $j < $c; $j++){
                    if (
                        $var and $token[$j + 1] == '{' and
                        ($token[$j] == '$' or (is_array($token[$j]) and $token[$j][1][0] == '$'))
                    )
                        $tmp++;
                    elseif ($token[$j] == '}' and !$tmp--)
                        break;

                    $var .= (is_array($token[$j]) ? $token[$j][1] : $token[$j]);
                }

                $tags[sprintf($mask, '$', $i)] = self::_var($var);;
                $skip = ($j - $i);
            } elseif ($opened){
                if ($token[$i] == '>'){
                    $opened = false;
                } elseif ($token[$i] == '/' and $token[$i + 1] == '>'){
                    $opened = false;
                    $skip = 1;
                } elseif (is_array($token[$i]) and trim($token[$i][1])){
                    $tmp = '';
                    $n = 1;

                    if ($token[$i + 1] == '='){
                        $n += 1;
                    } elseif (
                        in_array($token[$i + 1], array(':', '-', '.')) and
                        is_array($token[$i + 2]) and $token[$i + 3] == '='
                    ){
                        $token[$i][1] .= '_'.$token[$i + 2][1];
                        $n += 3;
                    } else {
                        continue;
                    }

                    for ($j = ($i + $n); $j < $c; $j++){
                        if (is_array($token[$j]) and !trim($token[$j][1])){
                            break;
                        } elseif ($token[$j] == '>'){
                            $j -= 1;
                            break;
                        } elseif ($token[$j] == '/' and $token[$j + 1] == '>'){
                            $j -= 2;
                            break;
                        }

                        $tmp .= (is_array($token[$j]) ? $token[$j][1] : $token[$j]);
                    }

                    if (in_array($tmp[0].substr($tmp, -1), array('""', "''", '``')))
                        $tmp = substr($tmp, 1, -1);

                    $tmp = self::parse($tmp);
                    $tmp = substr($tmp, (substr($tmp, 0, 5) == 'echo ' ? 5 : 0), -1);

                    if ($tmp[0].substr($tmp, -1) == "''")
                        $tmp = '`'.substr($tmp, 1, -1).'`';
                    elseif (substr($tmp, -3) == ".''")
                        $tmp = substr($tmp, 0, -3);

                    $tags[$key] += array($token[$i][1] => $tmp);
                    $skip = ($j - $i);
                } elseif (!is_array($token[$i]) and trim($token[$i]) and $token[$i + 1] == '='){
                    $tmp = (is_array($next = $token[$i + 2]) ? $next[1] : $next);

                    if (in_array($tmp[0].substr($tmp, -1), array('""', "''", '``')))
                        $tmp = substr($tmp, 1, -1);

                    $tags[$key] += array($token[$i] => $tmp);
                    $skip = 2;
                }
            } elseif ($token[$i] == '<'){
                if ($token[$i + 1] == '!'){
                    $tags[] = $token[$i];
                } elseif ($token[$i + 1] == '?'){
                    $tmp = '';
                    $is_php = (is_array($next = $token[$i + 2]) and (!trim($next[1]) or strtolower($next[1]) == 'php'));

                    for ($j = ($i + 2); $j < $c; $j++){                        if ($is_php and is_array($token[$j]) and $token[$j][0] == T_STRING){                            $next = $token[$j + 1];
                            $n = 0;

                            if (
                                ($next == '(' and ($n = 1)) or
                                (is_array($next) and !trim($next[1]) and $token[$j + ($n = 2)] == '(')
                            ){                                $method = '';

                                if (
                                    is_array($token[$j - 1]) and !trim($token[$j - 1][1]) and
                                    in_array($token[$j - 2][1], array('::', '->')) and
                                    is_array($token[$j - 3]) and !trim($token[$j - 3][1])
                                ){
                                    $method = $token[$j - 4][1].$token[$j - 3][1].$token[$j - 2][1].$token[$j - 1][1];
                                } elseif (
                                    is_array($token[$j - 2]) and !trim($token[$j - 2][1]) and
                                    in_array($token[$j - 3][1], array('::', '->'))
                                ){
                                    $method = $token[$j - 4][1].$token[$j - 3][1].$token[$j - 2][1];
                                } elseif (
                                    is_array($token[$j - 1]) and !trim($token[$j - 1][1]) and
                                    in_array($token[$j - 2][1], array('::', '->'))
                                ){
                                    $method = $token[$j - 3][1].$token[$j - 2][1].$token[$j - 1][1];
                                } elseif (in_array($token[$j - 1][1], array('::', '->'))){
                                    $method = $token[$j - 2][1].$token[$j - 1][1];
                                }

                                $token[$j][1] = '`'.$token[$j][1].'`';

                                if ($method){
                                    $tmp = substr($tmp, 0, -b::len($method));
                                    $method = str_replace(' ', '', $method);
                                    $method = explode((strpos($method, '::') ? '::' : '->'), $method);
                                    $method[0] = ($method[0][0] != '$' ? '`'.$method[0].'`' : $method[0]);
                                    $token[$j][1] = 'array('.$method[0].', '.$token[$j][1].')';
                                }

                                $tmp .= 'b::call('.$token[$j][1];
                                $tmp .= ($token[$j + $n + 1] == ')' ? '' : ', ');
                                $j += $n;
                                continue;                            }
                        } elseif ($token[$j] == '<' and $token[$j + 1] == '?'){
                            $j += 1;
                            break;
                        }

                        $tmp .= (is_array($token[$j]) ? $token[$j][1] : $token[$j]);
                    }

                    if ($is_php)
                        $tags[sprintf($mask, '@', $i)] = substr($tmp, (!trim($token[$i + 2][1]) ? 1 : 3));
                    else
                        $tags[] = '<?'.$tmp.'?>';

                    $skip = ($j - $i);
                } elseif ($token[$i + 1] == '/'){
                    $opened = false;
                    $key = '';

                    for ($j = ($i + 2), $end = array_search('>', $token); $j < $end; $j++)
                        if (is_array($token[$j])){
                            if (!trim($token[$j][1]))
                                break;

                            $key .= $token[$j][1];
                        } else {
                            if (!in_array($token[$j], array(':', '-', '.')))
                                break;

                            $key .= '_';
                        }

                    if (!is_array($scope[$key]) or (!$tmp = array_pop($scope[$key])))
                        $tmp = $i;

                    $tags[sprintf($mask, '/'.$key, $tmp)] = array();
                    $skip = ($j - $i);
                } elseif (trim(is_array($next = $token[$i + 1]) ? $next[1] : $next)){
                    $opened = true;
                    $key = '';

                    for ($j = ($i + 1), $end = array_search('>', $token); $j < $end; $j++)
                        if (is_array($token[$j])){
                            if (!trim($token[$j][1]))
                                break;

                            $key .= $token[$j][1];
                        } else {
                            if (!in_array($token[$j], array(':', '-', '.')))
                                break;

                            $key .= '_';
                        }

                    $key = sprintf($mask, $key, ($scope[$key][] = $i));
                    $tags[$key] = array();
                }
            } elseif (!$opened){
                $tags[] = (is_array($token[$i]) ? $token[$i][1] : $token[$i]);
            }

            for ($j = 0; $j <= $skip; $j++)
                unset($token[$i + $j]);

            $i += $skip;
            $skip = 0;
        }

        return $tags;
    }

////////////////////////////////////////////////////////////////////////////////

    static function parse($output){        $tags = (is_array($output) ? $output : self::tokenize($output));
        $result = '';
        $scope = array();
        $echo = false;

        foreach ($tags as $k => $v)
            if (is_int($k)){
                $result .= self::_vars((!$echo ? 'echo `'.$v : $v));
                $echo = true;
            } elseif ($k[0] == '$'){                $result .= self::_vars(!$echo ? 'echo '.$v.'.`' : '`.'.$v.'.`');
                $echo = true;
            } elseif ($k[0] == '@'){                $v = str_replace(self::char("'"), "'", $v);
                $result .= self::_vars(($echo ? '`;' : '').$v.';');
                $echo = false;
            } else {
                list($tag, $num) = explode(':', $k, 2);
                $result .= ($echo ? '`;' : '');

                if ($tag[0] == '/'){
                    $open = '';
                    $result .= $scope[substr($tag, 1)][$num];
                } else {
                    $v['#tag'] = '`'.$tag.'`';
                    $attr = $open = '';

                    foreach ($v as $k2 => $v2){                        if (b::function_exists($func = 'attr_'.$k2))
                            $open .= 'b::call(`'.$func.'`, ';

                        $attr .= '`'.$k2.'` => '.$v2.",\r\n";
                    }

                    $close = ($open ? str_repeat(')', substr_count($open, '(')) : '');

                    if (!isset($tags['/'.$k])){                        $attr = 'array('."\r\n".$attr.')';                        $result .= 'echo piles::call('.$open.$attr.$close.');';                    } else {                        $attr .= "`#text` => ob_get_clean()\r\n";
                        $attr = 'array('."\r\n".$attr.')';
                        $scope[$tag][$num] = 'echo piles::call('.$open.$attr.$close.');';
                        $result .= 'ob_start();';                    }
                }

                $echo = false;
            }

        if ($result and substr($result, -1) != ';')
            $result .= "`;";

        $result = str_replace(self::char("'"), "\'", $result);
        $result = str_replace('`', "'", $result);
        $result = @preg_replace('/%it\[(\d+)\]/e', "chr('\\1')'", $result);

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

}