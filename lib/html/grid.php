<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class HTML_Grid extends SQL_Etc {
    protected $data;
    protected $fields = array();
    protected $enum = array();


    function __construct($data, $fields){
        $this->data = $data;
        $this->fields = $fields;

        if (($order_by = $_GET['order_by']) and $order_by[0] == '-')
            $order_by = substr($order_by, 1);

        if (!$order_by or !$fields[$order_by])
            $_GET['order_by'] = '-'.$data->primary_key;

        if (!is_numeric($_GET['limit']))
            $_GET['limit'] = $data->limit;

        if (!is_numeric($_GET['page']))
            $_GET['page'] = 1;

        $data->order_by($_GET['order_by']);
        $data->limit($_GET['limit']);
        $data->page($_GET['page']);

        $class = clone $data;
        $class->order_by = array();
        $class->limit = $class->offset = 0;
        $query = sql::query($class->build('select').' procedure analyse()');

        foreach ($query->fetch() as $row){
            $type = $row['Optimal_fieldtype'];

            if (substr($type, 0, 4) != 'ENUM')
                continue;

            $name = substr($row['Field_name'], strpos($row['Field_name'], '.'));
            $name = substr($name, 1);
            $type = substr($type, 5);
            $type = substr($type, 0, strrpos($type, ')'));

            if (substr($name, 0, ($len = b::len($data->alias) + 1)) == $data->alias.'.')
                $name = substr($name, $len);

            foreach (token_get_all('<?php '.$type) as $k => $v)
                if ($k and is_array($v)){
                    $v = str_replace("\'", "'", substr($v[1], 1, -1));
                    $this->enum[$name][$v] = $v;
                }
        }
    }

////////////////////////////////////////////////////////////////////////////////

    function filter(){
        foreach ($this->fields as $k => $v)
            if ($k[0] == '_' or $this->data->relations[$k]){
            } elseif ($k[0] != '#'){
                    if (
                        in_array($cond[0], array('=', '!=', '<', '<=', '>', '>=', '%')) or
                        substr($cond, -1) == '%'
                    ){
                            $cond[0].substr($cond, -1) == '%%' or
                            $cond[0] == '%' or substr($cond, -1) == '%'
                        ){
                            $op = 'like';
                        } else {
                            $op = $cond[0];
                            $cond = substr($cond, 1);
                        }
                    } elseif ($this->enum[$k]){
                    } else {
                        $cond .= '%';

                    $this->data->where($k.' '.$op.' ?', $cond);
                }

                if (($v = $this->enum[$k]) and b::len($v) > 1){
                    $result .= '<th>'.html::dropdown('f['.$k.']', $v).'</th>';
                } elseif ($v){
                } else {
                    $result .= '<th><input name="f['.$k.']" type="text" /></th>';
                }
            }

        return '
            <form>
                <tr class="filter">'.$result.'</tr>
                <input name="order_by" type="hidden" />
                <input name="limit" type="hidden" />
                <input type="submit" style="display: none;" />
            </form>';

////////////////////////////////////////////////////////////////////////////////

    function head($arrows = array('asc' => '<span>▲</span>', 'desc' => '<span>▼</span>')){
        foreach ($this->fields as $k => $v){
            $order_by = $k;
            $class = preg_replace('/\W+/', '_', $k);
            $arrow = '';

            if ($_GET['order_by'] == '-'.$k){
                $order_by = $k;
                $class .= ($class ? ' ' : '').'desc';
                $arrow = $arrows['desc'];
            } elseif ($_GET['order_by'] == $k){
                $class .= ($class ? ' ' : '').'asc';
                $arrow = $arrows['asc'];

            parse_str($_SERVER['QUERY_STRING'], $query);
            array_walk_recursive($query, create_function('&$v', 'if ($v === "") $v = null;'));
            $query['order_by'] = $order_by;
            $query = '?'.http_build_query($query);

            if (
                $k[0] == '#' or $this->data->relations[$k] or
                ($this->enum[$k] and b::len($this->enum[$k]) < 2)
            )
                $result .= '<th class="'.$class.'">'.$v[0].'</th>';
            else
                $result .= '<th class="'.$class.'">'.$arrow.'<a href="'.$query.'">'.$v[0].'</a></th>';
        }

        return '<tr class="head">'.$result.'</tr>';
    }

////////////////////////////////////////////////////////////////////////////////

    function body(){
        $array = array();
        foreach ($data as $i => $row)
            foreach ($this->fields as $k => $v)
                $array[$i][$k] = (isset($row[$k]) ? array($row[$k], $v[1]) : $v);

        foreach ($array as $i => $row){

            foreach ($row as $k => $v){

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

    function pager($pager = 'simple'){
        $pager = b::call('tag_pager'.($pager ? '_'.$pager : ''), array(
            'count' => b::len($this->data),
            'limit' => $_GET['limit'],
            'page' => $_GET['page']
        ));

        return '<tr class="pager"><td colspan="'.b::len($this->fields).'">'.$pager.'</td></tr>';
    }

////////////////////////////////////////////////////////////////////////////////

    function __toString(){

////////////////////////////////////////////////////////////////////////////////

}