<?
class Artist extends SQL {
    public $has_many = array('songs');
}

class Song extends SQL {    public $has_one = array('artist');
    public $has_many = array('tests');
    public $has_and_belongs_to_many = array('albums');
}

class Album extends SQL {
    public $has_and_belongs_to_many = array('songs');
}

class Albums_Songs extends SQL {    //public $has_one = array('album', 'song');
}

class Test extends SQL {
    public $has_one = array('fPost');
}

class fPost extends SQL {
    public $table = 'nnn.forum_posts';
    public $has_one = array(
        'fposter' => array('poster_id', 'id'),
        'ftopic'  => array('topic_id', 'id')
    );
}

class fPoster extends SQL {
    public $table = 'nnn.forum_users';
    public $has_many = array('fposts' => array('id', 'poster_id'));
}

class fTopic extends SQL {
    public $table = 'nnn.forum_topics';
    public $has_many = array('fposts' => array('id', 'topic_id'));
}