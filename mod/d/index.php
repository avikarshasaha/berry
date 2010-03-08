<?
$post = new Post;
$post->select(
    '*', 'keywords.id', 'keywords.name',
    sql::from('comment')->select('count(*)')->where('post_id = post.id')
);
$post->order_by('-date', '-id');
$post->limit(10);
$post->page($_GET['page']);

//if (!$data = cache::get('piles/d.html', array('db' => $post))){    $data = piles::show('d', array(
        'data' => $post->fetch_array(),
        'count' => b::len(sql::table('posts'))
    ));
    //cache::set($data);
//}

echo $data;