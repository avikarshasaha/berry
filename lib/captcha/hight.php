<?
class hight {
////////////////////////////////////////////////////////////////////////////////

    // Hight | hight@inbox.ru
    // http://hight.fatal.ru/captcha/index.php?id=code
    function __construct($len, $symbols){
        $slen = (b::len($symbols) - 1);

        for ($i = 0; $i < $len; $i++)
            $captcha_key_array[] = $symbols[mt_rand(0, $slen)];

		$fonts = glob(dirname(__file__).'/fonts/*.ttf');
		$font = $fonts[array_rand($fonts)];

        $this->key = $captcha_key = implode('', $captcha_key_array);

        # Ширина-высота изображения
        $width = ($len * 25);
        $height = 50;

        # Создание изображения
        $image = imagecreatetruecolor($width, $height);

        # Рисуем фон
        $background  = imagecolorallocate($image, rand(230, 255), rand(230, 255), rand(230, 255));

        imagefill($image, 0, 0, $background);

        # Рисуем "шахматную" доску

        $cube_side = rand(8, 12); # размер грани квадратика

        $q = 0;

        while($q <= $height / $cube_side)
        {
            $i = 0;

            while($i <= $width)
            {
                if(fmod($q, 2))
                {             $cube_side = rand(8, 12); # Размер стороны квадратика             $color = imagecolorallocate($image, rand(150, 255), rand(150, 255), rand(150, 255));
                    imagefilledrectangle ($image, $i*2+$cube_side, $q*$cube_side, $i*2+$cube_side*2, $q*$cube_side+$cube_side, $color );
                }
                else
                {   $cube_side = rand(8, 12); # Размер стороны квадратика
                    $color = imagecolorallocate($image, rand(150, 255), rand(150, 255), rand(150, 255));
                    imagefilledrectangle ($image, $i*2, $q*$cube_side, $i*2+$cube_side, $q*$cube_side+$cube_side, $color );
                    //imagesetpixel($image, $i*2, $q*$cube_side, $black );
                    //imagefilledarc ($image, $i*2, $q*$cube_side, rand(8, 16), rand(5, 10), 0, 360, $color, IMG_ARC_PIE );
                    //imageellipse($image, $i*2, $q*$cube_side, 20, 10, $color );
                }

                $i = $i + $cube_side;
            }

            $q++;
        }


        # Рисуем строку
        $i = 10;

        foreach($captcha_key_array as $index => $value)
        {
            $x_position = rand(6, $height - 16);
            $str_color = imagecolorallocate($image, rand(50, 150), rand(50, 150), rand(50, 150));
            $captcha_key_array = imagettftext($image, 25, rand(-30, 30), $i, rand(25, 45), $str_color, $font, $value);
            $i = $i + rand(15, 25) ;
        }


        # Рисуем рамку
        $black = imagecolorallocate($image, 0, 0, 0);

        imageline($image, 0, 0, 0, $height, $black); # left
        imageline($image, $width - 1, 0, $width - 1, $height, $black); # right
        imageline($image, $width, 0, 0, 0, $black); # up
        imageline($image, $width, $height - 1, 0, $height - 1, $black); # down

        header('Cache-Control: private, no-cache="set-cookie"');
        header('Expires: 0');
        header('Pragma: no-cache');

        # Выводим изображение в браузер
        header('content-type: image/png');
        imagepng($image);

        # Высвобождаем ресурсы
        imagedestroy($image);
    }

////////////////////////////////////////////////////////////////////////////////
}