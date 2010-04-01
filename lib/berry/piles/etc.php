<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
abstract class Piles_Etc {    protected $filter, $file, $output;    protected static $cache = array();

////////////////////////////////////////////////////////////////////////////////

    static function show($string = '', $_ = array(), $filter = null){        $class = new Piles($string, $filter);
        return $class->render($_);
    }

////////////////////////////////////////////////////////////////////////////////

    static function stat(){        return (float)self::$cache['stat'];
    }

////////////////////////////////////////////////////////////////////////////////

    static function char($char){
        return '%char['.ord($char).']';
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

        if ($vars[$var[0]]){
            $ns = '$'.$vars[$var[0]];
            unset($var[0]);
        } elseif (!$ns){
            $ns = '$'.$var[0];
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
            if (is_array($v)){
                foreach ($v as $k2 => $v2)
                    $attr[$k.'_'.$k2] = $v2;

                unset($attr[$k]);
            }

        foreach ($attr as $k => $v)
            if (b::function_exists($func = 'attr_'.$k))
                $attr = b::call($func, $attr);

        foreach ($attr as $k => $v)
            if ($k[0] != '#'){
                $quote  = (is_int(strpos($v, '"')) ? "'" : '"');
                $attrs .= ' '.str_replace(array(':', '-', '.'), '_', $k).'='.$quote.$v.$quote;
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

    protected function call($attr = array()){        $escape = ($this->filter and !isset($this->filter[$attr['#tag']]));

        if ($this->filter and !$escape)
            foreach ($attr as $k => $v){
                if ($k[0] != '#' and !in_array($k, $this->filter[$attr['#tag']]))
                    unset($attr[$k]);
            }

        if ($attr['#skip'])
            return ($attr['#skip'] === true ? '' : $attr['#skip']);

        if ($escape or !b::function_exists($func = 'tag_'.$attr['#tag']))
            return self::fill($attr, $escape);

        return $attr['#before'].b::call($func, $attr).$attr['#after'];
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function _var($name){
        $var = explode('{', str_replace("'", "\'", $name), 2);
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

    protected static function _vars($output){
        if (preg_match_all('/(\$|\$\w+){([^}]*)}/sU', $output, $match))
            for ($i = 0, $c = b::len($match[0]); $i < $c; $i++)
                $output = str_replace($match[0][$i], self::_var($match[1][$i].'{'.$match[2][$i]), $output);

        return $output;
    }
////////////////////////////////////////////////////////////////////////////////

}