<?php
function tag_user_agent($attr){    $attr = array_merge(array(
	    'agent' => $_SERVER['HTTP_USER_AGENT']
	), $attr);

	if (!$attr['#text'])
        $attr['#text'] =
            tags::fill('img', array(
                'src'   => '~/tag/user_agent/os/%{user_agent.os}.png',
                'alt'   => '%{user_agent.agent}',
                'align' => 'absmiddle'
            )).
            tags::fill('img', array(
                'src'   => '~/tag/user_agent/browser/%{user_agent.browser}.png',
                'alt'   => '%{user_agent.agent}',
                'align' => 'absmiddle'
            ));

	$array = array('agent' => $attr['agent']);

	foreach (user_agent($attr['agent']) as $k => $v){	    $array[$k] = preg_replace('/[^a-z0-9_]/', '', strtolower($v));
	    $array['raw'][$k] = $v;
	}

	return tags::parse_lvars($attr, $array);}

////////////////////////////////////////////////////////////////////////////////

// http://punbb.org/forums/viewtopic.php?id=18304
function user_agent($useragent){
    $ua = strtolower($useragent);
    $ua_browser = 'unknown';
    $ua_os = 'unknown';

    // Browser detection:
    if (strpos($ua, 'amiga') !== false) $ua_os = 'Amiga';
    else if (strpos($ua, 'beos; ') !== false) $ua_os = 'BeOS';
    else if (strpos($ua, 'freebsd') !== false) $ua_os = 'FreeBSD';
    else if (strpos($ua, 'hp-ux') !== false) $ua_os = 'HP-UX';
    else if (strpos($ua, 'linux') !== false)
    {
    if (strpos($ua, 'centos') !== false || strpos($ua, 'cent os') !== false) $ua_os = 'CentOS';
    else if (strpos($ua, 'debian') !== false) $ua_os = 'Debian';
    else if (strpos($ua, 'fedora') !== false) $ua_os = 'Fedora';
    else if (strpos($ua, 'freespire') !== false) $ua_os = 'Freespire';
    else if (strpos($ua, 'gentoo') !== false) $ua_os = 'Gentoo';
    else if (strpos($ua, 'kanotix') !== false) $ua_os = 'Kanotix';
    else if (strpos($ua, 'kateos') !== false || strpos($ua, 'kate os') !== false) $ua_os = 'KateOS';
    else if (strpos($ua, 'knoppix') !== false) $ua_os = 'Knoppix';
    else if (strpos($ua, 'kubuntu') !== false) $ua_os = 'Kubuntu';
    else if (strpos($ua, 'linspire') !== false) $ua_os = 'Linspire';
    else if (strpos($ua, 'mandriva') !== false || strpos($ua, 'mandrake') !== false) $ua_os = 'Mandriva';
    else if (strpos($ua, 'redhat') !== false || strpos($ua, 'red hat') !== false) $ua_os = 'RedHat';
    else if (strpos($ua, 'slackware') !== false) $ua_os = 'Slackware';
    else if (strpos($ua, 'slax') !== false) $ua_os = 'Slax';
    else if (strpos($ua, 'suse') !== false) $ua_os = 'Suse';
    else if (strpos($ua, 'xubuntu') !== false) $ua_os = 'Xubuntu';
    else if (strpos($ua, 'ubuntu') !== false) $ua_os = 'Ubuntu';
    else if (strpos($ua, 'xandros') !== false) $ua_os = 'Xandros';
    else if (strpos($ua, 'arch') !== false) $ua_os = 'Arch';
    else if (strpos($ua, 'ark') !== false) $ua_os = 'Ark';
    else $ua_os = 'Linux';
    }
    else if (strpos($ua, 'macosx') !== false || strpos($ua, 'macos') !== false || strpos($ua, 'mac os x') !== false || strpos($ua, 'macintosh') !== false || strpos($ua, 'os=mac') !== false || strpos($ua, 'mac_osx') !== false) $ua_os = 'MacOSX';
    else if (strpos($ua, 'macppc') !== false || strpos($ua, 'mac_ppc') !== false || strpos($ua, 'cpu=ppc;') !== false && strpos($ua, 'os=mac') !== false || strpos($ua, 'macintosh; ppc') !== false || strpos($ua, 'macintosh;') !== false && strpos($ua, 'ppc') !== false || strpos($ua, 'mac_powerpc') !== false) $ua_os = 'MacPPC';
    else if (strpos($ua, 'netbsd') !== false) $ua_os = 'NetBSD';
    else if (strpos($ua, 'os/2') !== false) $ua_os = 'OS/2';
    else if (strpos($ua, 'avantgo') !== false) $ua_os = 'Palm';
    else if (strpos($ua, 'sunos') !== false || strpos($ua, 'solaris') !== false) $ua_os = 'SunOS';
    else if (strpos($ua, 'symbian') !== false) $ua_os = 'SymbianOS';
    else if (strpos($ua, 'unix') !== false) $ua_os = 'Unix';
    else if (strpos($ua, 'windows nt 6.0') !== false || strpos($ua, 'winnt6.0') !== false) $ua_os = 'WindowsVista';
    else if (strpos($ua, 'windows nt 5.1') !== false || strpos($ua, 'windows xp 5.1') !== false || strpos($ua, 'windows xp') !== false || strpos($ua, 'winxp') !== false || strpos($ua, 'winnt5.1') !== false || strpos($ua, 'cygwin_nt-5.1') !== false || strpos($ua, 'windows nt 5.0') !== false || strpos($ua, 'windows 2000') !== false || strpos($ua, 'win2000') !== false ||  strpos($ua, 'winnt5.0') !== false || strpos($ua, 'windows nt 5.2') !== false || strpos($ua, 'winnt5.2') !== false) $ua_os = 'WindowsXP';
    else if (strpos($ua, 'windows') !== false || strpos($ua, 'win') !== false) $ua_os = 'Windows';
    else if (strpos($ua, 'macintosh') !== false || strpos($ua, 'mac') !== false) $ua_os = 'Macintosh';
    else if (strpos($ua, 'sun') !== false) $ua_os = 'Sun';

    // Browser detection:
    if (strpos($ua, 'aweb') !== false) $ua_browser = 'AWeb';
    else if (strpos($ua, 'camino') !== false) $ua_browser = 'Camino';
    else if (strpos($ua, 'epiphany') !== false) $ua_browser = 'Epiphany';
    else if (strpos($ua, 'galeon') !== false) $ua_browser = 'Galeon';
    else if (strpos($ua, 'hotjava') !== false) $ua_browser = 'HotJava';
    else if (strpos($ua, 'icab') !== false) $ua_browser = 'iCab';
    else if (strpos($ua, 'safari') !== false) $ua_browser = 'Safari';
    else if (strpos($ua, 'konqueror') !== false) $ua_browser = 'Konqueror';
    else if (strpos($ua, 'flock') !== false) $ua_browser = 'Flock';
    else if (strpos($ua, 'iceweasel') !== false) $ua_browser = 'Iceweasel';
    else if (strpos($ua, 'seamonkey') !== false) $ua_browser = 'SeaMonkey';
    else if (strpos($ua, 'firefox') !== false) $ua_browser = 'Firefox';
    else if (strpos($ua, 'firebird') !== false) $ua_browser = 'Firebird';
    else if (strpos($ua, 'netscape') !== false) $ua_browser = 'Netscape';
    else if (strpos($ua, 'mozilla') !== false && strpos($ua, 'rv:') !== false) $ua_browser = 'Mozilla';
    else if (strpos($ua, 'opera') !== false) $ua_browser = 'Opera';
    else if (strpos($ua, 'avant browser') !== false) $ua_browser = 'AvantBrowser';
    else if (strpos($ua, 'maxthon') !== false || strpos($ua, 'myie') !== false) $ua_browser = 'Maxthon';
    else if (strpos($ua, 'phaseout') !== false) $ua_browser = 'PhaseOut';
    else if (strpos($ua, 'slimbrowser') !== false) $ua_browser = 'SlimBrowser';
    else if (strpos($ua, 'msie') !== false)
    {
        if (intval(substr($ua, strpos($ua, 'msie')+5)) > 6) $ua_browser = 'MSIE7';
        else $ua_browser = 'MSIE';
    }

    return array('browser' => $ua_browser, 'os' => $ua_os);}