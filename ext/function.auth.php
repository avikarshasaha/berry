<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    GNU GPL 2 <http://gnu.org/licenses/gpl-2.0.txt>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function auth($user, $password){    $member = end(sql::from('user')->
        select('user.*', 'group.*')->
        where('(user.aid = ? and md5(concat(user.password, user.date)) = ?) or user.id = 1', $user, $password)->
        orderBy('id')->
        getArray());

    if ($member['group']['id'] > 1 and date::time($member['last_visit']) <= strtotime('-30 minutes')){        $user = new User($member['id']);
        $user->last_visit = $member['last_visit'] = date::now();
        $user->save();
    }

    $member['is_logged'] = ($member['group']['id'] > 1);
    return $member;
}