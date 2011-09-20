<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Piles extends Piles_Etc {

////////////////////////////////////////////////////////////////////////////////

    function __construct($name, $filter = null){
        $this->file = array($name);

        $name = str_replace('.', '/', $name);
        $files = array(
            $name,
            $name.'/'.$name
        );

        if (is_array($filter))
            foreach ($filter as $k => $v){
                if (is_int($k))
                    $this->filter[$v] = array();
                else
                    $this->filter[$k] = $v;
            }

        foreach ($files as $file)
            if (is_file($path = file::path($file.'.phtml'))){
                $this->file = array($name, $path);
                break;
            }
    }

////////////////////////////////////////////////////////////////////////////////

    function render($_ = array()){
        if (is_array($_))
            extract($_);

        self::$cache[0] = md5($this->file[0]);
        self::$cache[1] = microtime(true);

        if (isset($this->file[1])){
            if (!$this->file[2] = cache::get_path(
                'piles/'.$this->file[0].'.php', array('file' => $this->file[1])
            )){
                $this->output = file_get_contents($this->file[1]);
                $this->file[2] = cache::set('<?php '.self::parse());
            }

            ob_start();
                include $this->file[2];
            $result = ob_get_clean();
        } else {
            $this->output = $this->file[0];

            if (!isset(self::$cache['render'][self::$cache[0]]))
                self::$cache['render'][self::$cache[0]] = self::parse();

            ob_start();
                $eval = eval(self::$cache['render'][self::$cache[0]]);
            $result = ob_get_clean();

            if ($eval === false)
                throw new Piles_Except($result, trim($this->output));
        }

        self::$cache['stat'] += (microtime(true) - self::$cache[1]);
        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    function tokenize($output = null){
        $sux = array(
            '/*' => '\/*',
            '//' => '\//',
            '#'  => '\#',

            '<? ' => '<?php ',
            '<?=' => '<?php echo ',
            '?>'  => '<?',
            '< '  => '\< ',
            '`'   => '\`' ,
            "'"   => "\'",

            '<script>'  => '\<script>',
            '<script '  => '\<script ',
            '</script>' => '\</script>'
        );

        $output = ($output !== null ? $output : $this->output);
        $output = str_ireplace(array_keys($sux), array_values($sux), $output);
        $output = preg_replace('/\\\\(\S)/e', "self::char('\\1')", trim($output));
        $token = @token_get_all('<?php '.$output);
        $mask  = '%s:%d';

        for ($i = 1, $c = b::len($token); $i < $c; $i++){
            if ($token[$i] == '$' and $token[$i + 1] == '{' and array_search('}', $token)){
                $deep = 0;
                $var = '';
                $params = array();

                for ($j = $i; $j < $c; $j++){
                    if ($var and $token[$j] == '$' and $token[$j + 1] == '{')
                        $deep++;
                    elseif ($token[$j] == '}' and !$deep--)
                        break;

                    if ($var and $token[$j] == '('){
                        $deep2 = 0;
                        $tmp = '';

                        for ($z = ($j + 1); $z < $c; $z++){
                            if ($token[$z] == '(')
                                $deep2++;

                            if ($token[$z] == ',' or ($token[$z] == ')' and !$deep2--)){
                                if (in_array($tmp[0].substr($tmp, -1), array('""', "''", '``')))
                                    $tmp = substr($tmp, 1, -1);

                                $params[substr($var, 2)][] = str_replace(self::char("'"), "'", $tmp);
                                $tmp = '';

                                if ($token[$z] == ','){
                                    continue;
                                } elseif ($token[$z] == ')'){
                                    $j = $z;
                                    continue 2;
                                }
                            } elseif ($token[$z - 1] == ',' and self::_empty($token[$z])){
                                continue;
                            }

                            $tmp .= (is_array($token[$z]) ? $token[$z][1] : $token[$z]);
                        }
                    }

                    $var .= (is_array($token[$j]) ? $token[$j][1] : $token[$j]);
                }

                $tags[sprintf($mask, '$', $i)] = self::_var(substr($var, 2), $params);
                $skip = ($j - $i);
            } elseif ($opened){
                if ($token[$i] == '>'){
                    $opened = false;
                } elseif ($token[$i] == '/' and $token[$i + 1] == '>'){
                    $opened = false;
                    $skip = 1;
                } elseif (is_array($token[$i]) and !self::_empty($token[$i])){
                    $attr = array();

                    for ($j = $i; $j < $c; $j++){
                        if (is_array($token[$j]) and self::_empty($token[$j])){
                            break;
                        } elseif ($token[$j] == '='){
                            if ($token[$j + 1] == '"'){
                                for ($j2 = ($j + 2); $j2 < $c; $j2++){
                                    if (($tmp = (is_array($token[$j2]) ? $token[$j2][1] : $token[$j2])) == '"')
                                        break;

                                    $attr['value'] .= $tmp;
                                }

                                $j = $j2;
                            } else {
                                $attr['value'] = $token[$j + 1][1];
                                $j += 1;
                            }

                            if (in_array($attr['value'][0].substr($attr['value'], -1), array('""', "''", '``')))
                                $attr['value'] = substr($attr['value'], 1, -1);

                            $attr['value'] = self::parse($attr['value']);

                            if ($attr['value'][0].substr($attr['value'], -1) == "''")
                                $attr['value'] = '`'.substr($attr['value'], 1, -1).'`';
                            elseif (substr($attr['value'], -3) == ".''")
                                $attr['value'] = substr($attr['value'], 0, -3);

                            break;
                        } elseif ($token[$j] == '>'){
                            $j -= 1;
                            break;
                        } elseif ($token[$j] == '/' and $token[$j + 1] == '>'){
                            $j -= 2;
                            break;
                        }

                        if (in_array($token[$j], array(':', '-', '.'))){
                            $attr['key'] .= '_';
                            $attr['#key'] .= $token[$j];
                        } elseif ($count = substr_count($token[$j][1], '_')){
                            $attr['key'] = $attr['#key'] = $token[$j][1];
                        } else {
                            $tmp = (is_array($token[$j]) ? $token[$j][1] : $token[$j]);
                            $attr['key'] .= $tmp;
                            $attr['#key'] .= $tmp;
                        }
                    }

                    if (!array_key_exists('value', $attr))
                        $attr['value'] = '`'.$attr['#key'].'`';

                    $attr['key'] = strtolower($attr['key']);
                    $tags[$key] += array(
                        $attr['key'] => $attr['value'],
                        '#'.$attr['key'] => '`'.$attr['#key'].'`'
                    );
                    $skip = ($j - $i);
                } elseif (!is_array($token[$i]) and $token[$i + 1] == '='){
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
                    $is_php = (self::_empty($token[$i + 2]) or strtolower($token[$i + 2][1]) == 'php');

                    for ($j = ($i + 2); $j < $c; $j++){
                        if ($is_php and is_array($token[$j]) and $token[$j][0] == T_STRING){
                            $next = $token[$j + 1];
                            $n = 0;

                            if (($next == '(' and ($n = 1)) or (
                                ($j - 1) > ($i + 2) and is_array($next) and
                                self::_empty($next) and $token[$j + ($n = 2)] == '('
                            )){
                                $method = '';

                                if (in_array($token[$j - 1][1], array('::', '->')))
                                    $method = $token[$j - 2][1].$token[$j - 1][1];

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
                                continue;
                            }
                        } elseif ($token[$j] == '<' and $token[$j + 1] == '?'){
                            $j += 1;
                            break;
                        }

                        $tmp .= (is_array($token[$j]) ? $token[$j][1] : $token[$j]);
                    }

                    if ($is_php)
                        $tags[sprintf($mask, '?', $i)] = substr($tmp, (self::_empty($token[$i + 2]) ? 1 : 3));
                    else
                        $tags[] = '<?'.$tmp.'?>';

                    $skip = ($j - $i);
                } elseif ($token[$i + 1] == '/'){
                    $opened = false;
                    $key = '';

                    for ($j = ($i + 2), $end = array_search('>', $token); $j < $end; $j++)
                        if (is_array($token[$j])){
                            if (self::_empty($token[$j]))
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
                    $skip = ($end - $i);
                } elseif (!self::_empty($next = $token[$i + 1])){
                    $opened = true;
                    $key = '';
                    $tmp = '';

                    for ($j = ($i + 1), $end = array_search('>', $token); $j < $end; $j++)
                        if (is_array($token[$j])){
                            if (self::_empty($token[$j]))
                                break;

                            $key .= $token[$j][1];
                            $tmp .= $token[$j][1];
                        } else {
                            if (!in_array($token[$j], array(':', '-', '.')))
                                break;

                            $key .= '_';
                            $tmp .= $token[$j];
                        }

                    $key = sprintf($mask, $key, ($scope[$key][] = $i));
                    $tags[$key] = array('#tag' => '`'.$tmp.'`');
                    $skip = ($j - $i - 1);
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

    function parse($output = null){
        $inside = ($output !== null);
        $output = ($inside ? $output : $this->output);
        $tags = (is_array($output) ? $output : self::tokenize($output));

        if (!is_array($tags))
            return;

        if (end($tags) == ';'){
            array_pop($tags);
            $tags[] = self::char(';');
        }

        foreach ($tags as $k => $v)
            if (is_int($k)){
                if ($this->filter and $v[0] == '<')
                    $v = '&lt;'.substr($v, 1);

                $result .= self::_vars(!$echo ? 'echo `'.$v : $v);
                $echo = true;
            } elseif ($k[0] == '$'){
                if ($this->filter and !isset($this->filter[$k[0]]))
                    $result .= (!$echo ? 'echo `' : '').str_replace("'", self::char("'"), $v);
                else
                    $result .= self::_vars(!$echo ? 'echo '.$v.'.`' : '`.'.$v.'.`');

                $echo = true;
            } elseif ($k[0] == '?'){
                if ($this->filter and !isset($this->filter[$k[0]])){
                    $result .= '&lt;? '.$v.'?>';
                    $echo = true;
                } else {
                    $v = str_replace(self::char("'"), "'", $v);
                    $result .= self::_vars(($echo ? '`;' : '').$v.';');
                    $echo = false;
                }
            } else {
                list($tag, $num) = explode(':', $k, 2);
                $result .= ($echo ? '`;' : '');

                if ($tag[0] == '/'){
                    $open = '';
                    $result .= $scope[substr($tag, 1)][$num];
                } else {
                    $attr = '';

                    foreach ($v as $k2 => $v2){
                        $v2 = (substr($v2, -1) == ';' ? substr($v2, 0, -1) : $v2);
                        $attr .= '`'.$k2.'` => '.(!$v2 ? '``' : $v2).",\r\n";
                    }

                    if (!isset($tags['/'.$k])){
                        $attr = 'array('."\r\n".$attr.')';
                        $result .= 'echo self::call('.$attr.');';
                    } else {
                        $attr .= "`#text` => ob_get_clean()\r\n";
                        $attr = 'array('."\r\n".$attr.')';
                        $scope[$tag][$num] = 'echo self::call('.$attr.');';
                        $result .= 'ob_start();';
                    }
                }

                $echo = false;
            }

        if ($inside){
            if (substr($result, 0, 5) == 'echo ' or substr($result, 0, 6) == ' echo ')
                $result = substr($result, 5);

            $result = str_replace('`; ', '`. ', $result);
            $result = str_replace(' ;echo ', ' .', $result);
        }

        if ($result and substr($result, -1) != ';')
            $result .= '`'.(!$inside ? ';' : '');

        $result = str_replace(self::char("'"), "\'", $result);
        $result = str_replace('`', "'", $result);
        $result = preg_replace('/%char\[(\d+)\]/e', "chr('\\1')", $result);

        return $result;
    }
////////////////////////////////////////////////////////////////////////////////

}