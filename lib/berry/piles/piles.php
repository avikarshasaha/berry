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
        $string = ($output ? $output : b::config('lib.b.show'));
        $string = str_replace('.', '/', $string);

        if (
            is_file($file = file::path('show/'.$string.'.phtml')) or
            is_file($file = file::path('show/'.$string.'/index.phtml'))
        ){            if (!self::$cache['show'] = cache::get_path('piles/'.$string.'.php', compact('file')))                self::$cache['show'] = cache::set('<?php '.self::parse(file_get_contents($file)));

            unset($output, $string, $file);
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
            throw new Piles_Except($result, self::$cache['show']);

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

    protected static function _var($name, $in_string = false){        $rand = md5(rand());        $var = explode('{', str_replace("'", "\'", $name), 2);
        $var[0] = substr($var[0], 1);
        $var[2] = strtr(self::varname(self::_vars($var[1], $rand)), array(
            '['.$rand.']'  => "'.",
            '[/'.$rand.']' => ".'"
        ));

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

            $var[2] = "b::call('".$func."', '".strtr(join('.', $tmp), array("'" => "\'"))."', get_defined_vars())";
        }

        if ($var[0] and b::function_exists($func = 'type_'.$var[0]))
            $var[2] = "b::call('".$func."', ".$var[2].')';

        if ($in_string)
            return '['.$in_string.']'.$var[2].'[/'.$in_string.']';

        return $var[2];
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function _vars($output, $in_string = false){
        if (preg_match_all('/(\$|\$\w+){([^}]*)}/sU', $output, $match))
            for ($i = 0, $c = b::len($match[0]); $i < $c; $i++)
                $output = str_replace($match[0][$i], self::_var($match[1][$i].'{'.$match[2][$i], $in_string), $output);

        return $output;
    }

////////////////////////////////////////////////////////////////////////////////

    static function parse($output){        $sux = array(
            '/*' => '\/*',
            '//' => '\//',
            '#'  => '\#',

            '<? ' => '<?php ',
            '?>' => '<?',

            '<script>' => '\<script>',
            '<script ' => '\<script ',
            '</script>' => '\</script>'
        );
        $output = str_ireplace(array_keys($sux), array_values($sux), $output);
        $output = preg_replace('/\\\\(\S)/e', "self::char('\\1')", trim($output));
        $token = token_get_all('<?php '.$output);
        $tags = $scope = array();
        $opened = false;
        $mask = '%s:%d';

        for ($i = 1, $c = b::len($token); $i < $c; $i++){
            if (
                ($token[$i] == '$' or (is_array($token[$i]) and $token[$i][1][0] == '$')) and
                $token[$i + 1] == '{' and array_search('}', $token)
            ){
                $tmp = 0;
                $var = '';

                for ($j = $i; $j < $c; $j++){                    if (
                        $var and $token[$j + 1] == '{' and
                        ($token[$j] == '$' or (is_array($token[$j]) and $token[$j][1][0] == '$'))
                    )
                        $tmp++;
                    elseif ($token[$j] == '}' and !$tmp--)                        break;

                    $var .= (is_array($token[$j]) ? $token[$j][1] : $token[$j]);
                }

                $var = self::_var($var);

                if (end($tags) == '('){
                    unset($tags[key($tags)]);

                    $tmp = 0;
                    $var = '('.$var;

                    for ($j += 1; $j < $c; $j++){
                        $var .= (is_array($token[$j]) ? $token[$j][1] : $token[$j]);

                        if ($token[$j] == '(')
                            $tmp++;
                        elseif ($token[$j] == ')' and !$tmp--)
                            break;
                    }
                }

                $tags[sprintf($mask, '$', $i)] = $var;
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

                    if ($token[$i + 1] == '='){                        $n += 1;
                    } elseif (
                        in_array($token[$i + 1], array(':', '-', '.')) and
                        is_array($token[$i + 2]) and $token[$i + 3] == '='
                    ){                        $token[$i][1] .= '_'.$token[$i + 2][1];
                        $n += 3;
                    } else {                        continue;                    }

                    for ($j = ($i + $n); $j < $c; $j++){
                        if (is_array($token[$j]) and !trim($token[$j][1])){
                            break;
                        } elseif ($token[$j] == '>'){                            $j -= 1;                            break;
                        } elseif ($token[$j] == '/' and $token[$j + 1] == '>'){                            $j -= 2;
                            break;
                        }

                        $tmp .= (is_array($token[$j]) ? $token[$j][1] : $token[$j]);
                    }

                    if ($tmp[0].substr($tmp, -1) == '""' or $tmp[0].substr($tmp, -1) == "''")
                        $tmp = substr($tmp, 1, -1);

                    $tags[$key] += array($token[$i][1] => $tmp);
                    $skip = ($j - $i);
                } elseif (!is_array($token[$i]) and trim($token[$i]) and $token[$i + 1] == '='){
                    $tmp = (is_array($token[$i + 2]) ? $token[$i + 2][1] : $token[$i + 2]);

                    if ($tmp[0].substr($tmp, -1) == '""' or $tmp[0].substr($tmp, -1) == "''")
                        $tmp = substr($tmp, 1, -1);

                    $tags[$key] += array($token[$i] => $tmp);
                    $skip = 2;
                }
            } elseif ($token[$i] == '<'){                if ($token[$i + 1] == '!'){
                    $tags[] = $token[$i];
                } elseif ($token[$i + 1] == '?'){
                    $tmp = '';

                    for ($j = ($i + 2); $j < $c; $j++){
                        if (!trim($tmp) and $token[$j] == '='){
                            $tmp .= 'echo ';
                            continue;
                        } elseif ($token[$j] == '<' and $token[$j + 1] == '?'){
                            $j++;
                            break;
                        }

                        $tmp .= (is_array($token[$j]) ? $token[$j][1] : $token[$j]);
                    }

                    if (is_array($token[$i + 2]) and (!trim($token[$i + 2][1]) or strtolower($token[$i + 2][1]) == 'php'))
                        $tags[sprintf($mask, '@', $i)] = substr($tmp, 3);
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
                } elseif (trim(is_array($token[$i + 1]) ? $token[$i + 1][1] : $token[$i + 1])){
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

        $result = '';
        $scope = array();
        $echo = false;
        $rand = md5(rand());

        foreach ($tags as $k => $v)
            if (is_int($k)){
                $v = str_replace("'", "\'", $v);
                $result .= self::_vars((!$echo ? "echo '".$v : $v), $rand);
                $echo = true;
            } elseif ($k[0] == '$'){                $result .= self::_vars(!$echo ? 'echo '.$v.".'" : "'.".$v.".'");
                $echo = true;
            } elseif ($k[0] == '@'){
                $result .= self::_vars(($echo ? "';" : '').$v.';');
                $echo = false;
            } else {
                list($tag, $num) = explode(':', $k, 2);
                $result .= ($echo ? "';" : '');

                if ($tag[0] == '/'){
                    $open = '';
                    $tag = substr($tag, 1);

                    foreach ($scope[$tag][$num] as $ak => $av)
                        if (b::function_exists($func = 'attr_'.$ak))
                            $open .= "b::call('".$func."', ";

                    $attr = str_replace(self::char("'"), "\'", var_export($scope[$tag][$num], true));
                    $attr = self::_vars($attr, $rand);
                    $close = ($open ? str_repeat(')', substr_count($open, '(')) : '');
                    $result .= 'echo piles::call('.$open.$attr.$close.');';
                } else {
                    $v['#tag'] = $tag;

                    if (!isset($tags['/'.$k])){
                        $open = '';

                        foreach ($v as $ak => $av)
                            if (b::function_exists($func = 'attr_'.$ak))
                                $open .= "b::call('".$func."', ";

                        $attr = str_replace(self::char("'"), "\'", var_export($v, true));
                        $attr = self::_vars($attr, $rand);
                        $close = ($open ? str_repeat(')', substr_count($open, '(')) : '');
                        $result .= 'echo piles::call('.$open.$attr.$close.');';
                    } else {
                        $v['#text'] = '['.$rand.']ob_get_clean()[/'.$rand.']';
                        $scope[$tag][$num] = $v;
                        $result .= 'ob_start();';
                    }
                }

                $echo = false;
            }

        if ($result and substr($result, -1) != ';')
            $result .= "';";

        $result = str_replace(array('['.$rand.']', '[/'.$rand.']', "''.", ".''"), array("'.", ".'", '', ''), $result);
        $result = preg_replace('/%it\[(\d+)\]/e', "chr('\\1')", $result);

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

}