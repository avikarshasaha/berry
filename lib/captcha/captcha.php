<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class captcha {    const LEN = 4;
    const SYMBOLS = '23456789abcdeghkmnpqsuvxyz';
////////////////////////////////////////////////////////////////////////////////

    static function zz($len = self::LEN, $symbols = self::SYMBOLS){        $slen = (b::len($symbols) - 1);
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

    static function kcaptcha($len = self::LEN, $symbols = self::SYMBOLS){
        $captcha = new kcaptcha;
        $captcha->width = ($len * 25);
        $captcha->allowed_symbols = $symbols;
        $captcha->genstring($len);
        $_SESSION['captcha'] = $captcha->keystring;
        $captcha->genimage();
    }

////////////////////////////////////////////////////////////////////////////////

    static function checker($check){
        return ($check == $_SESSION['captcha']);
    }

////////////////////////////////////////////////////////////////////////////////

    static function __call($method, $args){
        self::kcaptcha(8);
    }

////////////////////////////////////////////////////////////////////////////////

    static function captcha3D($len = 'easy'){        $url = 'http://blockspam.ru/Captcha.ashx?type='.$len;        list($url, $texr) = explode("\r\n", file_get_contents($url));
        $_SESSION['captcha'] = strtolower($texr);        http::go($url);    }

////////////////////////////////////////////////////////////////////////////////

    static function hight($len = self::LEN, $symbols = self::SYMBOLS){        $captcha = new hight($len, $symbols);
        $_SESSION['captcha'] = $captcha->key;
    }
}