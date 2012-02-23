<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru/>                            | ~ )\
    <http://berry.goodgirl.ru/license/>                    /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru/>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
abstract class Piles_Etc extends Object {
    protected $filter, $file, $output;
    protected static $cache = array();

////////////////////////////////////////////////////////////////////////////////

    static function show($string = '', $_ = array(), $filter = null){
        $class = new Piles($string, $filter);
        return $class->render($_);
    }

////////////////////////////////////////////////////////////////////////////////

    static function stat(){
        return (float)self::$cache['stat'];
    }

////////////////////////////////////////////////////////////////////////////////

    static function char($char){
        return '%pileschar:'.ord($char).'%';
    }

////////////////////////////////////////////////////////////////////////////////

    static function varname($string, $ns = ''){
        if (isset(self::$cache['varname'][$ns][$string]))
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

        if ($vars[$var[0]] or !$ns){
            $ns = ($vars[$var[0]] ? $vars[$var[0]] : $var[0]);
            $ns = '$ {\''.str_replace("'", self::char("'"), $var[0]).'\'}';

            unset($var[0]);
        }

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

    static function fill($tag, $attr = array(), $escape = false){
        if (is_array($tag))
            list($tag, $attr, $escape) = array($tag['#tag'], $tag, $attr);

        foreach ($attr as $k => $v)
            if (is_array($v) and !$attr['#tag']){
                foreach ($v as $k2 => $v2)
                    $attr[$k.'_'.$k2] = $v2;

                unset($attr[$k]);
            }

        foreach ($attr as $k => $v)
            if (b::function_exists($func = 'attr_'.$k))
                $attr = b::call($func, $attr);

        if ($attr['#skip'])
            return ($attr['#skip'] === true ? '' : $attr['#skip']);

        foreach ($attr as $k => $v)
            if ($k[0] != '#' and !is_array($v)){
                $quote  = (strpos($v, '"') !== false ? "'" : '"');
                $attrs .= ' '.(isset($attr['#'.$k]) ? $attr['#'.$k] : $k).'='.$quote.$v.$quote;
            }

        $open = ($escape ? '&lt;' : '<');
        $cont = array_key_exists('#text', $attr);
        $result  = $attr['#before'].$open.$tag.$attrs.(!$cont ? ' /' : '').'>';
        $result .= ($cont ? $attr['#text'].$open.'/'.$tag.'>' : '');
        $result .= $attr['#after'];

        return $result;
    }

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

    protected function call($attr = array()){
        $escape = ($this->filter and !isset($this->filter[$attr['#tag']]));

        if ($this->filter and !$escape)
            foreach ($attr as $k => $v){
                if ($k[0] != '#' and !in_array($k, $this->filter[$attr['#tag']]))
                    unset($attr[$k]);
            }

        if ($escape or !b::function_exists($func = 'tag_'.str_replace(array(':', '-', '.'), '_', $attr['#tag'])))
            return self::fill($attr, $escape);

        return $attr['#before'].b::call($func, $attr).$attr['#after'];
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function _var($name, $params = null){
        $var = str_replace('\.', self::char('.'), $name);
        $var = array_reverse(explode('.', $var));
        $funcs = array();

        foreach ($var as $k => $v){
            if (!b::function_exists($func = 'method_'.$v))
                break;

            $tmp = (array)$params[join('.', array_reverse($var))];
            $tmp = str_replace('%', '%%', var_export($tmp, true));

            array_unshift($funcs, sprintf('b::call(`%s`, %%s, %s, get_defined_vars())', $func, $tmp));
            unset($var[$k]);
        }

        if (b::function_exists($func = 'var_'.end($var))){
            array_unshift($funcs, sprintf('b::call(`%s`, `%%s`, get_defined_vars())', $func));
            array_pop($var);

            $var = array_reverse($var);
            $var = join('.', $var);
        } else {
            $var = array_reverse($var);
            $var = join('.', $var);
            $var = self::varname($var);
        }

        foreach ($funcs as $v)
            $var = sprintf($v, $var);

        return self::_vars($var, true);
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function _vars($output, $quoted = false){
        if (preg_match_all('/\${([^}]*)}/sU', $output, $match))
            for ($i = 0, $c = count($match[0]); $i < $c; $i++){
                $var = self::_var($match[1][$i]);
                $var = ($quoted ? "'.".$var.".'" : $var);
                $output = str_replace($match[0][$i], $var, $output);
            }

        return $output;
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function _empty($token){
        return ($token[0] == T_WHITESPACE);
    }

////////////////////////////////////////////////////////////////////////////////

}
