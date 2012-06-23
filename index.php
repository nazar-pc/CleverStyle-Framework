<?php
/****************************************************************************\
| Minimal requirements for full-featured work:								*|
|	1) Versions of server software:											*|
|		* Apache Web Server				>= 2								*|
|		* PHP							>= 5.4;								*|
|			Presence of libraries PHP:										*|
|			* mcrypt					>= 2.4								*|
|			* iconv															*|
|			* mbstring														*|
|			* cURL															*|
|		* MySQL							>= 5.0.7;							*|
|	2) Browsers versions:													*|
|		* Opera Internet Browser		>= 11.10;							*|
|		* Microsoft Internet Explorer	>= 10;								*|
|		* Google Chrome					>= 11;								*|
|			(Webkit 534.24+)												*|
|		* Safari						>= 5;								*|
|			(Webkit 534.24+)												*|
|		* Mozilla Firefox				>= 4;								*|
\****************************************************************************/

//Задаем время старта выполнения для использования при необходимости как текущего времени
define('MICROTIME',	microtime(true));					//Время в секундах (с плавающей точкой)
define('TIME',		round(MICROTIME));					//Время в секундах (целое число)
define('CHARSET',	'utf-8');							//Основная кодировка
define(
	'FS_CHARSET',										//Кодировка файловой системы (названий файлов) (изменять при наличии проблемм)
	strtolower(PHP_OS) == 'winnt' ? 'windows-1251' : 'utf-8'
);
define('DS',		DIRECTORY_SEPARATOR);				//Алиас для системной константы разделителя путей
define('PS',		PATH_SEPARATOR);					//Алиас для системной константы разделителя папок включений
define('OUT_CLEAN',	false);								//Включить захват вывода (для безопасности)
OUT_CLEAN && ob_start();								//Захват вывода для избежания вывода нежелательных данных
require_once __DIR__.DS.'core'.DS.'functions.php';		//Подключение библиотеки базовых функций
define('DIR',		path_to_str(__DIR__));				//Алиас корневой папки сайта
chdir(DIR);
_require(DIR.DS.'core'.DS.'loader.php', true, true);	//Передача управления загрузчику движка