<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    LGPL <http://www.gnu.org/licenses/lgpl.txt>           / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class captcha {    const len = 4;
    const symbols = '23456789abcdeghkmnpqsuvxyz';
////////////////////////////////////////////////////////////////////////////////

    function zz($len = self::len, $symbols = self::symbols){        $slen = (b::len($symbols) - 1);
        for ($i = 0; $i < $len; $i++)
            $rand .= $symbols[mt_rand(0, $slen)];

        header('Content-type: image/png');

        $captcha = new zz;
        $captcha->width = ($len * 25);
        $captcha->size = 18;
        $_SESSION['captcha'] = $rand;
        $captcha->makeimg($rand);
    }

////////////////////////////////////////////////////////////////////////////////

    function kcaptcha($len = self::len, $symbols = self::symbols){
        $captcha = new kcaptcha;
        $captcha->width = ($len * 25);
        $captcha->allowed_symbols = $symbols;
        $captcha->genstring($len);
        $_SESSION['captcha'] = $captcha->keystring;
        $captcha->genimage();
    }

////////////////////////////////////////////////////////////////////////////////

    function checker($check){
        return ($check == $_SESSION['captcha']);
    }

////////////////////////////////////////////////////////////////////////////////

    function __call($method, $args){
        self::kcaptcha(8);
    }

////////////////////////////////////////////////////////////////////////////////

    function captcha3D($len = 'easy'){        $url = 'http://blockspam.ru/Captcha.ashx?type='.$len;        list($url, $texr) = explode("\r\n", file_get_contents($url));
        $_SESSION['captcha'] = strtolower($texr);        http::go($url);    }

////////////////////////////////////////////////////////////////////////////////

    function hight($len = self::len, $symbols = self::symbols){        $captcha = new hight($len, $symbols);
        $_SESSION['captcha'] = $captcha->key;
    }
}