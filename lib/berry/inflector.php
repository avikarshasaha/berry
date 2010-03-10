<?php
// http://kohanaphp.com/
class Inflector {
	protected static $cache = array();
	protected static $uncountable;
	protected static $irregular;

////////////////////////////////////////////////////////////////////////////////

	static function uncountable($string){
		if (!isset(self::$uncountable)){
			self::$uncountable = b::config('lib.inflector.uncountable');
			self::$uncountable = array_combine(self::$uncountable, self::$uncountable);
		}

		return isset(self::$uncountable[strtolower($string)]);
	}

////////////////////////////////////////////////////////////////////////////////

	static function singular($string){
		$string = strtolower(trim($string));

		$key = 'singular_'.$string;

		if (isset(self::$cache[$key]))
			return self::$cache[$key];

		if (self::uncountable($string))
			return self::$cache[$key] = $string;

		if (!isset(self::$irregular))
			self::$irregular = b::config('lib.inflector.irregular');

		if ($irregular = array_search($string, self::$irregular))
			$string = $irregular;
		elseif (preg_match('/[sxz]es$/', $string) or preg_match('/[^aeioudgkprt]hes$/', $string))
			$string = substr($string, 0, -2);
		elseif (preg_match('/[^aeiou]ies$/', $string))
			$string = substr($string, 0, -3).'y';
		elseif (substr($string, -1) === 's' and substr($string, -2) !== 'ss')
			$string = substr($string, 0, -1);

		return self::$cache[$key] = $string;
	}

////////////////////////////////////////////////////////////////////////////////

	static function plural($string){
		$string = strtolower(trim($string));

		$key = 'plural_'.$string;

		if (isset(self::$cache[$key]))
			return self::$cache[$key];

		if (self::uncountable($string))
			return self::$cache[$key] = $string;

		if (!isset(self::$irregular))
			self::$irregular = b::config('lib.inflector.irregular');

		if (isset(self::$irregular[$string]))
			$string = self::$irregular[$string];
		elseif (preg_match('/[sxz]$/', $string) or preg_match('/[^aeioudgkprt]h$/', $string))
			$string .= 'es';
		elseif (preg_match('/[^aeiou]y$/', $string))
			$string = substr_replace($string, 'ies', -1);
		else
			$string .= 's';

		return self::$cache[$key] = $string;
	}

////////////////////////////////////////////////////////////////////////////////

	static function camelize($string){
		$string = 'x'.strtolower(trim($string));
		$string = ucwords(preg_replace('/[\s_]+/', ' ', $string));

		return substr(str_replace(' ', '', $string), 1);
	}

////////////////////////////////////////////////////////////////////////////////

	static function underscore($string){
		// http://cakephp.org/
		return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', trim($string)));
	}

////////////////////////////////////////////////////////////////////////////////

	static function humanize($string){
		return preg_replace('/[_-]+/', ' ', trim($string));
	}

////////////////////////////////////////////////////////////////////////////////

	static function tableize($string){
		return self::plural(self::singular($string));
	}

////////////////////////////////////////////////////////////////////////////////

}