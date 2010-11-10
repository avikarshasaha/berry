<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Service_LJ {    protected $server = 'www.livejournal.com';
////////////////////////////////////////////////////////////////////////////////

	function __construct($username, $password, $journal = ''){
		$this->username = $username;
		$this->password = md5($password);
		$this->journal  = ($journal ? $journal : $username);
	}

////////////////////////////////////////////////////////////////////////////////

    protected function _request($method, $params = array()){    	$params = array_merge(array(
    	    'username'  => $this->username,
    	    'hpassword' => $this->password,
    	    'ver'       => 1
    	), $params);

    	$response = xmlrpc::request(
    	    $this->server,
    	    '/interface/xmlrpc',
    	    'LJ.XMLRPC.'.$method,
    	    array(xmlrpc::prepare($params)),
    	    'Putoberry' // Лужкову бы понравилось
    	);

    	unset($this->error);

    	if ($response[0])
    	    return $response[1];

    	$this->error = $response[1]['faultString'];
    }

////////////////////////////////////////////////////////////////////////////////

	function event($method, $params = array(), $timestamp = 0){	    $timestamp = date::time($timestamp);	    $params = array_merge(array(
	        'itemid' => 0,

	        'lineendings' => 'unix',
	        'usejournal'  => $this->journal,
	        'security' => 'public',

    	    'year' => date('Y', $timestamp),
    	    'mon'  => date('m', $timestamp),
    	    'day'  => date('d', $timestamp),
    	    'hour' => date('H', $timestamp),
    	    'min'  => date('i', $timestamp)
	    ), $params);

    	$params['subject'] = base64_encode($params['subject']);
    	$params['subject type'] = 'base64';
    	$params['event'] = base64_encode($params['event']);
    	$params['event type'] = 'base64';

    	if ($result = $this->_request($method.'event', $params))
    		return $result;
	}

////////////////////////////////////////////////////////////////////////////////

	function postEvent($subject, $event, $timestamp = 0){
        return $this->event('post', compact('subject', 'event'), $timestamp);
	}

////////////////////////////////////////////////////////////////////////////////

	function editEvent($itemid, $subject, $event, $timestamp = 0){
    	return $this->event('edit', compact('itemid', 'subject', 'event'), $timestamp);
	}

////////////////////////////////////////////////////////////////////////////////

	function getUserTags(){    	$result = $this->_request('getUserTags', array('usejournal' => $this->journal));
    	return $result['tags'];
	}

////////////////////////////////////////////////////////////////////////////////

	function getFriendOf(){    	$result = $this->_request('friendof');
    	return $result['friendofs'];
	}

////////////////////////////////////////////////////////////////////////////////

	function getFriendGroups(){    	$result = $this->_request('getfriendgroups');
    	return $result['friendgroups'];
	}

////////////////////////////////////////////////////////////////////////////////

	function getFriends(){        $result = $this->_request('getfriends');
        return $result['friends'];
	}

////////////////////////////////////////////////////////////////////////////////

}