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

    function __construct($string = ''){        $name = ($string ? $string : b::config('lib.b.show'));
        $name = str_replace('.', '/', $name);
        $files = array('ext/'.$name, 'mod/'.$name, 'lib/berry/'.$name, 'lib/'.$name);

        foreach ($files as $file)
            if (
                is_file($path = file::path($file.'.phtml')) or
                is_file($path = file::path($file.'/index.phtml'))
            ){
                $this->file = array($name, $path);
                return;
            }

        $this->file = array($string);    }

////////////////////////////////////////////////////////////////////////////////

    function render(){        if (is_array($_ = $this->vars))
            extract($_);

        self::$cache[0] = md5($this->file[0]);
        self::$cache[1] = microtime(true);

        if (isset($this->file[1])){            if (!$this->file[2] = cache::get_path(
                'piles/'.$this->file[0].'.php', array('file' => $this->file[1])
            )){
                $this->output = file_get_contents($this->file[1]);
                cache::set('<?php '.self::parse());
            }

            ob_start();
                include $this->file[2];
            $result = ob_get_clean();        } else {            $this->output = $this->file[0];

            if (!isset(self::$cache['render'][self::$cache[0]]))
                self::$cache['render'][self::$cache[0]] = self::parse();
            ob_start();
                $eval = eval(self::$cache['render'][self::$cache[0]]);
            $result = ob_get_clean();

            if ($eval === false)
                throw new Piles_Except($result, trim($this->output));        }

        self::$timer += (microtime(true) - self::$cache[1]);
        return $result;    }

////////////////////////////////////////////////////////////////////////////////

    function __toString(){        return self::render();    }

////////////////////////////////////////////////////////////////////////////////

    function tokenize($output = null){
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

        $output = ($output !== null ? $output : $this->output);
        $output = str_ireplace(array_keys($sux), array_values($sux), $output);
        $output = preg_replace('/\\\\(\S)/e', "self::char('\\1')", trim($output));
        $token = @token_get_all('<?php '.$output);
        $mask  = '%s:%d';

        for ($i = 1, $c = b::len($token); $i < $c; $i++){
            $is_var = ($token[$i] == '$' or (is_array($token[$i]) and $token[$i][1][0] == '$'));

            if (
                $is_var and is_array($token[$i + 1]) and
                $token[$i + 1][1][0] == '{' and ($pos = strpos($token[$i + 1][1], '}'))
            ){
                $var = (is_array($token[$i]) ? $token[$i][1] : $token[$i]);
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

                    for ($j = ($i + 2); $j < $c; $j++){
                        if ($is_php and is_array($token[$j]) and $token[$j][0] == T_STRING){
                            $next = $token[$j + 1];
                            $n = 0;

                            if (
                                ($next == '(' and ($n = 1)) or
                                (is_array($next) and !trim($next[1]) and $token[$j + ($n = 2)] == '(')
                            ){
                                $method = '';

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
                                continue;
                            }
                        } elseif ($token[$j] == '<' and $token[$j + 1] == '?'){
                            $j += 1;
                            break;
                        }

                        $tmp .= (is_array($token[$j]) ? $token[$j][1] : $token[$j]);
                    }

                    if ($is_php)
                        $tags[sprintf($mask, '?', $i)] = substr($tmp, (!trim($token[$i + 2][1]) ? 1 : 3));
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
                    $skip = ($end - $i);
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

    function parse($output = null){        $output = ($output !== null ? $output : $this->output);
        $tags = (is_array($output) ? $output : self::tokenize($output));

        if (end($tags) == ';')
            $tags[] = ' ';

        if (is_array($filter = $this->tags))
            foreach ($filter as $k => $v)
                if (is_int($k)){
                    $filter[$v] = array();
                    unset($filter[$k]);
                }

        foreach ($tags as $k => $v)
            if (is_int($k)){
                if (is_array($filter) and $v[0] == '<')
                    $v = '&lt;'.substr($v, 1);

                $result .= self::_vars(!$echo ? 'echo `'.$v : $v);
                $echo = true;
            } elseif ($k[0] == '$'){
                if (is_array($filter) and !isset($filter[$k[0]]))
                    $result .= (!$echo ? 'echo `' : '').str_replace("'", self::char("'"), $v);
                else
                    $result .= self::_vars(!$echo ? 'echo '.$v.'.`' : '`.'.$v.'.`');

                $echo = true;
            } elseif ($k[0] == '?'){
                if (is_array($filter) and !isset($filter[$k[0]])){
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
                    $attr = '`#tag` => `'.$tag.'`,'."\r\n";
                    $open = '';

                    foreach ($v as $k2 => $v2){
                        if (is_array($filter[$tag]) and !in_array($k2, $filter[$tag]))
                            continue;

                        if (b::function_exists($func = 'attr_'.$k2))
                            $open .= 'b::call(`'.$func.'`, ';

                        $attr .= '`'.$k2.'` => '.(!$v2 ? '``' : $v2).",\r\n";
                    }

                    if (is_array($filter) and !isset($filter[$tag])){
                        $close = ', true';
                    } else {
                        $open = '';
                        $close = ($open ? str_repeat(')', substr_count($open, '(')) : '');
                    }

                    if (!isset($tags['/'.$k])){
                        $attr = 'array('."\r\n".$attr.')';
                        $result .= 'echo piles::call('.$open.$attr.$close.');';
                    } else {
                        $attr .= "`#text` => ob_get_clean()\r\n";
                        $attr = 'array('."\r\n".$attr.')';
                        $scope[$tag][$num] = 'echo piles::call('.$open.$attr.$close.');';
                        $result .= 'ob_start();';
                    }
                }

                $echo = false;
            }

        if ($result and substr($result, -1) != ';')
            $result .= '`;';

        $result = str_replace(self::char("'"), "\'", $result);
        $result = str_replace('`', "'", $result);
        $result = @preg_replace('/%char\[(\d+)\]/e', "chr('\\1')'", $result);

        return $result;
    }
////////////////////////////////////////////////////////////////////////////////

}