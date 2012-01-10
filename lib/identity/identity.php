<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru/>                            | ~ )\
    <http://berry.goodgirl.ru/license/>                    /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru/>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class IDentity {    
    protected $config = array();

////////////////////////////////////////////////////////////////////////////////

    function __construct($config){
        $this->config = $config;
        $this->config['base_url'] = 'http://'.parse_url($config['url'], PHP_URL_HOST);
    }

////////////////////////////////////////////////////////////////////////////////

    function auth($identity){
        $identity = strtolower($identity);
        $identity = (strpos($identity, '@') ? $identity : self::_scheme($identity));

        if ($_GET['oauth_token'] or $_GET['session'] or $_GET['openid_identity'])
            return;

        if (
            strpos($identity, 'http://twitter.com') === 0 or
            strpos($identity, 'http://www.twitter.com') === 0
        ){
            include_once 'twitteroauth.php';

            $config = $this->config['twitter'];
            $twitter = new TwitterOAuth($config['key'], $config['secret']);
            $request = $twitter->getRequestToken($this->config['url']);
            $redirect = $twitter->getAuthorizeURL($request['oauth_token']);

            $_SESSION['IDentity']['twitter'] = array(
                'key' => $request['oauth_token'],
                'secret' => $request['oauth_token_secret']
            );

            return $redirect;
        }

        if (
            strpos($identity, 'http://facebook.com') === 0 or
            strpos($identity, 'http://www.facebook.com') === 0        
        ){
            include_once 'facebook.php';

            $config = $this->config['facebook'];
            $facebook = new Facebook(array(
                'appId' => $config['id'],
                'secret' => $config['secret']
            ));

            return $facebook->getLoginUrl(array(
                'next' => $this->config['url'],
                'cancel_url' => $this->config['url'].(strpos($this->config['url'], '?') ? '&' : '?').'openid_mode=cancel',
                'req_perms' => ($this->config['mail'] ? 'email' : null)
            ));
        }
        
        include_once 'LightOpenID.php';
        
        $openid = new LightOpenID($this->config['base_url']);
        $openid->identity = self::provider($identity);
        $openid->returnUrl = $this->config['url'];
        $openid->required = array(
            'namePerson', 'namePerson/first', 'namePerson/last', 'namePerson/friendly',
            ((!strpos($identity, '@') and $this->config['mail']) ? 'contact/email' : null)
        );
        
        try {
            $redirect = $openid->authUrl();

            $_SESSION['IDentity']['openid'] = $identity;
        } catch (ErrorException $e){
            throw new IDentity_Except($identity, 404);
        }
        
        if ($identity == 'http://mail.ru' or strpos($identity, '@mail.ru')){
            $pos1 = strpos($redirect, 'openid.identity');
            $pos2 = strpos($redirect, '&', $pos1);
            $tmp1 = substr($redirect, 0, $pos1);
            $tmp2 = substr($redirect, $pos2);
            $redirect = $tmp1.'openid.identity='.urlencode($identity).$tmp2;

            $pos1 = strpos($redirect, 'openid.claimed_id');
            $pos2 = strpos($redirect, '&', $pos1);
            $tmp1 = substr($redirect, 0, ($pos1 - 1));
            $tmp2 = substr($redirect, $pos2);
            $redirect = $tmp1.$tmp2;
        }

        return $redirect;
    }

////////////////////////////////////////////////////////////////////////////////

    function data(){
        $storage = $_SESSION['IDentity'];

        if ($_GET['oauth_token']){
            include_once 'twitteroauth.php';

            //unset($_SESSION['IDentity']['twitter']);

            $config = $this->config['twitter'];
            $storage = $storage['twitter'];
            $twitter = new TwitterOAuth(
                $config['key'], $config['secret'],
                $storage['key'], $storage['secret']
            );
            $data = $twitter->getAccessToken($_GET['oauth_verifier']);

            if ($twitter->http_code != 200)
                throw new IDentity_Except('http://twitter.com', $twitter->http_code);

            $udata = simplexml_load_file('http://twitter.com/users/'.$data['screen_name']);

            return array(
                'id' => $data['user_id'],
                'aid' => $data['screen_name'],
                'name' => (string)$udata->name,
                'identity' => 'http://twitter.com/'.$data['screen_name'],
                'provider' => 'http://twitter.com'
            );
        }

        if ($_GET['session']){
            include_once 'facebook.php';

            //unset($_SESSION['IDentity']['facebook']);

            $config = $this->config['facebook'];
            $storage = $storage['facebook'];
            $facebook = new Facebook(array(
                'appId' => $config['id'],
                'secret' => $config['secret']
            ));

            try {
                $data = $facebook->api('/me');
            } catch (FacebookApiException $e){
                throw new IDentity_Except('http://www.facebook.com', 403);
            }

            if (!$data['aid'] = $data['username'])
                $data['aid'] = str::translit($data['name'], ' ');

            return array(
                'id' => $data['id'],
                'aid' => $data['aid'],
                'name' => $data['name'],
                'mail' => $data['email'],
                'identity' => $data['link'],
                'provider' => 'http://www.facebook.com'
            );
        }
        
        if ($_GET['openid_identity']){
            include_once 'LightOpenID.php';
            
            //unset($_SESSION['IDentity']['openid']);

            $identity = $storage['openid'];            
            $openid = new LightOpenID($this->config['base_url']);           
            $data = $openid->getAttributes();

            if (!$openid->validate())
                throw new IDentity_Except($identity, 403);
                
            if ($data['namePerson'])
                $data['name'] = $data['namePerson'];
            elseif ($data['namePerson/first'] or $data['namePerson/last'])
                $data['name'] = trim($data['namePerson/first'].' '.$data['namePerson/last']);

            if (!$data['aid'] = $data['namePerson/friendly'])
                $data['aid'] = str::translit($data['name'], ' ');

            if (strpos($identity, '@'))
                $data['email'] = $identity;
            elseif ($data['contact/email'])
                $data['email'] = $data['contact/email'];

            if (strpos($openid->data['openid_op_endpoint'], 'http://www.livejournal.com') === 0){
                $udata = simplexml_load_file($identity.'/data/atom');

                $data['name'] = (string)$udata->author->name;

                $udata = $udata->xpath('//lj:journal');
                $udata = $udata[0]->attributes();

                $data['id'] = (string)$udata->userid;
                $data['aid'] = (string)$udata->username;
            }

            return array(
                'id' => $data['id'],
                'aid' => $data['aid'],
                'name' => $data['name'],
                'mail' => $data['email'],
                'identity' => $identity,
                'provider' => 'http://'.parse_url($openid->data['openid_op_endpoint'], PHP_URL_HOST)
            );
        }
    }

////////////////////////////////////////////////////////////////////////////////

    static function provider($provider){
        $map = array(
            'yandex.ru' => 'openid.yandex.ru',
            'rambler.ru' => 'id.rambler.ru/users/%user@%host',
            'mail.ru' => 'openid.mail.ru/mail/%user',

            'google.com' => 'google.com/profiles/me',
            'googlemail.com' => 'google.com/profiles/me',
            'yahoo.com' => 'me.yahoo.com',
            //'flickr.com' => 'me.yahoo.com',
            'aol.com' => 'openid.aol.com',
            'steam.com' => 'steamcommunity.com/openid',

            'vkontakte.ru' => 'vkontakteid.ru',
            'vk.com' => 'vkontakteid.ru',
            'vk.lc' => 'vkontakteid.ru',

            'livejournal.com' => '%user.livejournal.com',
            'bulyon.com' => '%user.livejournal.com',
            'liveinternet.ru' => 'www.liveinternet.ru/users/%user',
            'li.ru' => 'www.liveinternet.ru/users/%user',
            'diary.ru' => 'diary.ru/~%user'
        );

        $provider = strtolower(self::_scheme($provider));
        $url = parse_url($provider);
        $scheme = $url['scheme'].'://';
        $user = $url['user'];
        $host = (substr($url['host'], 0, 4) == 'www.' ? substr($url['host'], 4) : $url['host']);

        if ($user){
            if ($map[$host]){
                $provider = $map[$host];
            } elseif ($mx = dns_get_record($host, DNS_MX)){
                $tmp = explode('.', $mx[0]['target']);
                $tmp = array_slice($tmp, -2, 2);
                $provider = $map[join('.', $tmp)];
            }
        } elseif ($map[$host] and strpos('%user', $map[$host]) === false){
            $provider = $map[$host];
        }

        $provider = self::_scheme($provider);
        $provider = str::format($provider, compact('user', 'host'));
        $provider = str_replace('://.', '://', $provider);

        return $provider;
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _scheme($s){
        return (strpos($s, '://') === false ? 'http://' : '').$s;
    }

////////////////////////////////////////////////////////////////////////////////

}
