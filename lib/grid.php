<?php
class Grid extends SQL_Etc {
    protected $data, $fields = array();
////////////////////////////////////////////////////////////////////////////////

    function __construct($data, $fields){
        $this->data = $data;
        $this->fields = $fields;
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _order_by(){
        if (($order_by = $_GET['order_by']) and $order_by[0] == '-')
            $order_by = substr($order_by, 1);

        if (!$order_by or !$this->fields[$order_by])
            $_GET['order_by'] = '-'.$this->data->primary_key;

        $this->data->order_by($_GET['order_by']);
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _limit(){
        if (!is_numeric($_GET['limit']))
            $_GET['limit'] = $this->data->limit;

        $this->data->limit($_GET['limit']);
        $this->data->page((int)$_GET['page']);
    }

////////////////////////////////////////////////////////////////////////////////

    function filter(){        /*$schema = array();
        foreach ($this->data->schema() as $k => $v)
            if ($this->fields[$k])
                $schema[$k] = $v['type'];*/

        foreach ($this->fields as $k => $v)
            if ($k[0] == '_' or $this->data->relations[$k]){                $result .= '<th>&nbsp;</th>';
            } elseif ($k[0] != '#'){                if ($cond = $_GET['f'][$k]){
                    if (
                        in_array($cond[0], array('=', '!=', '<', '<=', '>', '>=', '%')) or
                        substr($cond, -1) == '%'
                    ){                        if (
                            $cond[0].substr($cond, -1) == '%%' or
                            $cond[0] == '%' or substr($cond, -1) == '%'
                        ){
                            $op = 'like';
                        } else {
                            $op = $cond[0];
                            $cond = substr($cond, 1);
                        }
                    } else {                        $op = 'like';
                        $cond .= '%';                    }

                    $this->data->where($k.' '.$op.' ?', $cond);
                }

                $result .= '<th><input name="f['.$k.']" type="text" /></th>';
            }

        return '
            <form>
                <tr class="filter">'.$result.'</tr>
                <input name="order_by" type="hidden" />
                <input name="limit" type="hidden" />
                <input type="submit" style="display: none;" />
            </form>';    }

////////////////////////////////////////////////////////////////////////////////

    function head(){        self::_order_by();
        self::_limit();

        foreach ($this->fields as $k => $v){            $v = $this->fields[$k] = (array)$v;
            $order_by = $k;
            $class = preg_replace('/\W+/', '_', $k);

            if ($_GET['order_by'] == '-'.$k){
                $order_by = $k;
                $class .= ($class ? ' ' : '').'desc';
            } elseif ($_GET['order_by'] == $k){                $order_by = '-'.$k;
                $class .= ($class ? ' ' : '').'asc';            }

            parse_str($_SERVER['QUERY_STRING'], $query);
            array_walk_recursive($query, create_function('&$v', 'if ($v === "") $v = null;'));
            $query['order_by'] = $order_by;
            $query = '?'.http_build_query($query);

            if ($k[0] == '#' or $this->data->relations[$k])
                $result .= '<th class="'.$class.'">'.$v[0].'</th>';
            else
                $result .= '<th class="'.$class.'"><img alt="" /><a href="'.$query.'">'.$v[0].'</a></th>';
        }

        return '<table class="grid"><tr class="head">'.$result.'</tr>';
    }

////////////////////////////////////////////////////////////////////////////////

    function body(){        $data = $this->data->fetch_array();
        $array = array();
        foreach ($data as $i => $row)
            foreach ($this->fields as $k => $v)
                $array[$i][$k] = (isset($row[$k]) ? array($row[$k], $v[1]) : $v);

        foreach ($array as $i => $row){            $result .= '<tr class="'.($i % 2 == 0 ? 'odd' : 'even').'">';

            foreach ($row as $k => $v){                $v = ($v[1] ? $v[1] : $v[0]);

                if (b::function_exists($v))
                    $result .= '<td>'.b::call($v, $data[$i]).'</td>';
                else
                    $result .= '<td>'.str::format($v, $data[$i]).'</td>';
            }

            $result .= '</tr>';
        }

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    function foot(){        $pager = b::call('tag_pager_simple', array(
            'count' => b::len($this->data),
            'limit' => $_GET['limit'],
            'page' => $_GET['page']
        ));

        return '</table><div class="pager">'.$pager.'</div>';
    }

////////////////////////////////////////////////////////////////////////////////

}