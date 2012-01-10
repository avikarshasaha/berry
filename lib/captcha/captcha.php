<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru/>                            | ~ )\
    <http://berry.goodgirl.ru/license/>                    /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru/>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Captcha {    const LEN = 4;
    const SYMBOLS = '23456789abcdeghkmnpqsuvxyz';

////////////////////////////////////////////////////////////////////////////////

    static function is_valid($id, $value){
        return ($value == $_SESSION['lib']['captcha'][$id]);
    }

////////////////////////////////////////////////////////////////////////////////

    static function __call($method, $args){
        self::kcaptcha(8);
    }
////////////////////////////////////////////////////////////////////////////////

    static function kcaptcha($id, $len = self::LEN, $symbols = self::SYMBOLS){
        $captcha = new kcaptcha;
        $captcha->width = ($len * 25);
        $captcha->allowed_symbols = $symbols;
        $captcha->genstring($len);
        $_SESSION['lib']['captcha'][$id] = $captcha->keystring;
        $captcha->genimage();
    }

////////////////////////////////////////////////////////////////////////////////

    static function hights($id, $len = self::LEN, $symbols = self::SYMBOLS){        $captcha = new hight($len, $symbols);
        $_SESSION['lib']['captcha'][$id] = $captcha->key;
    }

////////////////////////////////////////////////////////////////////////////////
}
