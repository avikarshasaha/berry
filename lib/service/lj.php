<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    LGPL <http://www.gnu.org/licenses/lgpl.txt>           / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Service_LJ {
////////////////////////////////////////////////////////////////////////////////

	function __construct($username, $password, $journal = ''){
		$this->username = $username;
		$this->password = md5($password);
		$this->journal  = ($journal ? $journal : $username);
	}

////////////////////////////////////////////////////////////////////////////////

    private function _request($method, $params = array()){    	$params = array_merge(array(
    	    'username'  => $this->username,
    	    'hpassword' => $this->password,
    	    'ver'       => 1
    	), $params);

    	$response = xmlrpc::request(
    	    'www.livejournal.com',
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

	function event($method, $subject, $event, $time = 0, $itemid = 0){
    	$params['subject'] = base64_encode($subject);
    	$params['subject type'] = 'base64';
    	$params['event'] = base64_encode($event);
    	$params['event type'] = 'base64';
    	$params['itemid'] = $itemid;
    	$params['lineendings'] = 'unix';
    	$params['usejournal'] = $this->journal;

    	$time = (!is_numeric($time) ? strtotime($time) : $time);
    	$time = ($time > 0 ? $time : time());

	    $params['year'] = date('Y', $timestamp);
	    $params['mon']  = date('m', $timestamp);
	    $params['day']  = date('d', $timestamp);
	    $params['hour'] = date('H', $timestamp);
	    $params['min']  = date('i', $timestamp);

    	if ($result = $this->_request($method.'event', $params))
    		return ($request['itemid'] * 256 + $request['anum']);
	}

////////////////////////////////////////////////////////////////////////////////

	function postEvent($subject, $event, $timestamp = 0){
        return $this->event('post', $subject, $event, $timestamp);
	}

////////////////////////////////////////////////////////////////////////////////

	function editEvent($itemid, $subject, $event, $timestamp = 0){
    	return $this->event('edit', $subject, $event, $timestamp, $itemid);
	}

////////////////////////////////////////////////////////////////////////////////

	function getUserTags(){		$params['usejournal'] = $this->journal;

    	if ($result = $this->_request('getusertags', $params))
    		return $result['tags'];
	}

////////////////////////////////////////////////////////////////////////////////

	function getFriendOf($limit = 0){
    	$params['friendoflimit'] = $limit;

    	if ($result = $this->_request('friendof', $params))
    		return $result['friendofs'];
	}

////////////////////////////////////////////////////////////////////////////////

	function getFriendGroups(){
    	if ($result = $this->_request('getfriendgroups'))
    		return $result['friendgroups'];
	}

////////////////////////////////////////////////////////////////////////////////

	function getFriends($limit = 0, $friendofs = true, $groups = true, $bdays = true){
    	$params['friendlimit'] = $limit;
    	$params['includefriendof'] = $friendofs;
    	$params['includegroups'] = $groups;
    	$params['includebdays'] = $bdays;

    	if ($result = $this->_request('getfriends', $params))
    	    return $result;
	}

////////////////////////////////////////////////////////////////////////////////

}