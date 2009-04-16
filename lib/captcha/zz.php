<?php

/*
	----------------------------------
	2z project | http://2z-project.com
	----------------------------------
*/

class zz {

	var $width	=	85;
	var $height	=	25;
	var $size	=	15;

	function makeimg($number) {	    // Блин, не пойму, ребяты.
	    // Это же класс. Причём не статичный, а значит абстракция.
	    // Почему всё в одном методе? Почему конфиги не отдельно?
	    // Воровать не удобно и править приходиться, а не наследтовать!!!11111
		$fonts = glob(dirname(__file__).'/fonts/*.ttf');
		$font = $fonts[array_rand($fonts)];

		$image = '';
		$image = ImageCreate($this->width, $this->height);

		$bg = ImageColorAllocate($image, 255, 255, 255);
		$fg = ImageColorAllocate($image, 0, 0, 0);

		ImageColorTransparent($image, $bg);
		ImageInterlace($image, 1);

		$this->msg = $number;
		ImageTTFText($image, $this->size, rand(-5, 5), rand(5, 20), 20, $fg, $font, $this->msg);

		$dc = ImageColorAllocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
		ImageArc($image, rand(0, $this->width ), rand(0, $this->height ), rand($this->width / 2, $this->width), rand($this->height / 2, $this->height), 0, 360, $dc);

		$dc = ImageColorAllocate($image, rand(0,255), rand(0, 255), rand(0, 255));
		ImageArc($image, rand(0, $this->width ), rand(0, $this->height ), rand($this->width / 2, $this->width), rand($this->height / 2, $this->height), 0, 360, $dc);

		$dots = $this->width * $this->height / 10;
		for ($i=0; $i < $dots; $i++) {
			$dc = ImageColorAllocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
			ImageSetPixel($image, rand(0, $this->width), rand(0, $this->height), $dc);
		}

		ImagePNG($image);

		imagedestroy($image);
	}
}
?>