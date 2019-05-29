<?php

namespace Database;

/**
 * 自动加载类
 */
class DatabaseLoad
{
	/**
	 * 注册一个自动加载器
	 */
	public static function register_autoloader()
	{
		return spl_autoload_register(['Database\DatabaseLoad', 'autoloader']);
	}

	/**
	 * 自动加载器
	 *
	 * @param string $className
	 */
	public static function autoloader($class)
	{
		if (strpos('Database', $class) !== false) {
			return;
		}

		$file = substr(dirname(__FILE__), 0, -strlen('Database')) . str_replace('\\', '/', $class) . '.php';

		if (file_exists($file)) {
			include($file);
		}
	}
}