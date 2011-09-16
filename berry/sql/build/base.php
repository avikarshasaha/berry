<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
abstract class SQL_Build_Base extends SQL_Etc {
    protected $o;

////////////////////////////////////////////////////////////////////////////////

    function __construct($object){
        $this->o = $object;
    }

////////////////////////////////////////////////////////////////////////////////

    protected function tokenize($query, $keywords){
        $token = token_get_all('<?php '.$query);
        $result = $array = array();

        array_walk($keywords, create_function('&$v', 'if ($v === null) $v = "";'));

        for ($i = 1, $c = b::len($token); $i < $c; $i++){
            if (
                (
                    $token[$i + 1][0] == T_WHITESPACE and
                    isset($keywords[$key = strtolower($token[$i][1].' '.$token[$i + 2][1])]) and
                    $token[$i + 3][0] == T_WHITESPACE and $i += 2
                ) or
                (isset($keywords[$key = strtolower($token[$i][1])]) and $token[$i + 1][0] == T_WHITESPACE)
            ){
                if (!isset($result[$key])){
                    $result[$key] = array();
                    $array = &$result[$key];
                } else {
                    $tmp = '';
                    $count = 0;

                    for ($j = $i; $j < $c; $j++){
                        if ($token[$j] == '(')
                            $count++;

                        $tmp .= (is_array($token[$j]) ? $token[$j][1] : $token[$j]);

                        if ($token[$j] == ')' and !$count--)
                            break;
                    }

                    $array[] = $tmp;
                    $i = $j;
                }

                continue;
            }

            if (is_array($token[$i])){
                end($array);
                $key = key($array);
                $key = ($key === null ? 0 : $key);

                for ($j = $i; $j < $c; $j++)
                    if ($token[$j - 1] == '['){
                        array_pop($array);
                        end($array);
                        $key = key($array);

                        for ($j2 = $j; $j2 < $c; $j2++){
                            $tmp = (is_array($token[$j2]) ? $token[$j2][1] : $token[$j2]);
                            $array[$key] .= $tmp;

                            if ($tmp == ']')
                                break;
                        }

                        $j = $j2;
                    } elseif ($token[$j][0] == T_WHITESPACE){
                        break;
                    } elseif ($token[$j + 1] == '('){
                        $array[] = $token[$j][1].'(';
                        $j += 1;
                    } elseif ($token[$j + 1] == '.'){
                        $array[$key] .= $token[$j][1].'.';
                        $j += 1;
                    } else {
                        if (!is_array($array[$key]) and substr($array[$key], -1) == '.'){
                            $array[$key] = array(substr($array[$key], 0, -1), $token[$j][1]);
                        } else {
                            $array[] = (is_array($token[$j]) ? $token[$j][1] : $token[$j]);
                            $array[] = '';
                        }

                        break;
                    }

                $i = $j;
            } else {
                $array[] = $token[$i];
                $array[] = '';
            }
        }

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    protected function rebuild($tokens, $keywords){
        $tokens = (!is_array($tokens) ? $this->tokenize($tokens, $keywords) : $tokens);

        foreach ($tokens as $key => $value)
            foreach ($value as $k => $v)
                if (is_array($v)){
                    $tmp = explode('.', $v[0]);
                    $name = '';

                    for ($i = 0, $c = b::len($tmp); $i < $c; $i++){
                        $name .= strtolower($tmp[$i]);

                        if (
                            (!$relation = $this->o->relations[$name]) and
                            (!$relation = $this->o->relations[$name = inflector::singular($name)])
                        )
                            break;

                        $name .= '.';
                        $local = $relation['local'];
                        $foreign = $relation['foreign'];
                        $table = ($foreign['table1'] ? $foreign['table1'] : $foreign['table']);

                        if ($pos = strpos($table, '.'))
                            $table = array(substr($table, 0, $pos), substr($table, ($pos + 1)));
                        else
                            $table = '`'.$table.'`';

                        if (isset($tokens['select'])){
                            if ($foreign['alias1']){
                                $foreign['alias'] = $foreign['alias1'];
                                $foreign['field'] = $foreign['field1'];
                            }

                            if ($foreign['alias'] and in_array($foreign['alias'], $tokens['from']))
                                continue;

                            $this->o->join(substr($name, 0, -1));

                            $tokens['from'] = array_merge($tokens['from'], array(
                                'left', 'join', $table, 'as', $foreign['alias'],
                                'on', '(',
                                    array($foreign['alias'], $foreign['field']),
                                    '=',
                                    array($local['alias'], $local['field']),
                                ')'
                            ));

                            if ($relation['type'] == 'has_and_belongs_to_many'){
                                $table2 = $foreign['table2'];

                                if ($pos = strpos($table2, '.'))
                                    $table2 = array(substr($table2, 0, $pos), substr($table2, ($pos + 1)));
                                else
                                    $table2 = '`'.$table2.'`';

                                $tokens['from'] = array_merge($tokens['from'], array(
                                    'left', 'join', $table2, 'as', $foreign['alias2'],
                                    'on', '(',
                                        array($foreign['alias2'], $foreign['field2']),
                                        '=',
                                        array($foreign['alias1'], $foreign['field3']),
                                    ')'
                                ));
                            }
                        } elseif (isset($tokens['using'])){
                            if (!in_array($table, $tokens['using'])){
                                $tokens['using'][] = ',';
                                $tokens['using'][] = $table;
                            }
                        } else {
                            if (!in_array($table, $tokens['from'])){
                                $tokens['from'][] = ',';
                                $tokens['from'][] = $table;
                            }
                        }
                    }
                }

        foreach ($tokens as $key => $value){
            $array = array();
            $quote = false;

            foreach ($value as $k => $v){
                if (!is_array($v) and trim($v) === '')
                    continue;

                if (is_array($v)){
                    if (strtolower(end($array)) == 'as'){
                        $array[] = '`'.$v[0].'.'.$v[1].'`';
                    } else {
                        if ($key != 'select' or strtolower($value[$k + 1]) == 'as')
                            $array[] = '`'.$v[0].'`.'.$v[1];
                        else
                            $array[] = '`'.$v[0].'`.'.$v[1].' as `'.$v[0].'.'.$v[1].'`';
                    }
                } elseif ($v == '`'){
                    if (!$quote)
                        $array[] = $v.$value[$k + 2].$v;
                    else
                        array_pop($array);

                    $quote = !$quote;
                } elseif (strtolower(end($array)) == 'as'){
                    $array[] = '`'.$v.'`';
                } elseif (
                    strtolower($v) == 'as' or !preg_match('/^\w+$/i', $v) or is_numeric($v) or
                    in_array(strtolower($v), (array)$keywords[$key])
                ){
                    $array[] = $v;
                } elseif (in_array($key, array('from', 'insert', 'update', 'delete'))){
                    $array[] = '`'.$v.'`';
                } elseif (end($array) == '?'){
                    array_pop($array);
                    $array[] = '?'.$v;
                } else {
                    $array[] = ($this->o->alias ? '`'.$this->o->alias.'`.' : '').'`'.$v.'`';
                }
            }

            $result[$key] = join(' ', $array).$result[$key];
        }

        $key = key($keywords);

        foreach ($tokens as $k => $v)
            if ($key == $k or (($tmp = trim($result[$k]) and $tmp != '( )')))
                $query .= "\r\n$k ".$result[$k];

        return $query;
    }

////////////////////////////////////////////////////////////////////////////////

}