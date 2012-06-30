<?php
global $Objects, $timeload, $loader_init_memory, $interface;
$timeload['start'] = MICROTIME;
$interface = true;
error_reporting(E_ALL | E_STRICT);
//error_reporting(0);
header('Content-Type: text/html; charset=utf-8');
header("Connection: close");
mb_internal_encoding('utf-8');
//Убиваем небезопасные глобальные переменные, использование GET метода для передачи переменных не рекомендуется
//Вместо GET используйте POST
$_GET						= [];
$_REQUEST					= [];
//Задание базовых констант с путями системных папок
//DOMAIN - константа, содержащая базовый домен сайта
//CDOMAIN - константа, содержащая домен текущего сайта
//(он может отличатся от базового домена, если вы находитесь на зеркале)
define('CDOMAIN',		$_SERVER['HTTP_HOST']);		//Доменное имя текущего сайта
define('CONFIG',		DIR.DS.'config');			//Папка конфигурации
define('CORE',			DIR.DS.'core');				//Папка ядра
	define('CLASSES',	CORE.DS.'classes');			//Папка с классами
	define('ENGINES',	CORE.DS.'engines');			//Папка с движками БД и хранилищ
	define('LANGUAGES',	CORE.DS.'languages');		//Папка с языковыми файлами
define('INCLUDES',		DIR.DS.'includes');			//Папка с включениями
	define('CSS',		INCLUDES.DS.'css');			//Папка с CSS стилями
	define('IMG',		INCLUDES.DS.'img');			//Папка с изображениями
	define('JS',		INCLUDES.DS.'js');			//Папка с JavaScript скриптами
define('TEMPLATES',		DIR.DS.'templates');		//Папка с шаблонами
define('COMPONENTS',	DIR.DS.'components');		//Папка для компонентов
	define('BLOCKS',	COMPONENTS.DS.'blocks');	//Папка для блоков
	define('MODULES',	COMPONENTS.DS.'modules');	//Папка для модулей
	define('PLUGINS',	COMPONENTS.DS.'plugins');	//Папка для плагинов
define('STORAGES',		DIR.DS.'storages');			//Локальное хранилище
	define('PCACHE',	STORAGES.DS.'pcache');		//Папка с публичным кешем (доступным пользователю извне)
define('THEMES',		DIR.DS.'themes');			//Папка с темами

//Load information about minimal needed Software versions
_require(CORE.DS.'required_verions.php', true, true);
//Including of custom user files
_include(DIR.DS.'custom.php', true, false);

$timeload['loader_init']	= microtime(true);
$loader_init_memory			= memory_get_usage();
//Запуск ядра и первичных классов, создание необходимых объектов
//ВНИМАНИЕ: Отключение создания следующих объектов или изменение порядка почти на 100% приведет к полной неработоспособности движка!!!
//При необходимости изменения логики работы первычных классов движка используйте пользовательские версии файлов, не изменяя порядок загрузки
$Objects					= new \cs\Objects;			//Объект подключения и выгрузки классов
$Objects->load([
	'cs\\Error',									//Объект обработки ошибок
	'cs\\Core',										//Объект ядра движка (проверка путей и функции шифрования)
	'cs\\Cache',									//Объект системного кеша
	'_cs\\Text',									//Объект поддержки мультиязычного текстового контента
	['cs\\Language',	'L'],						//Объект музьтиязычности
	'cs\\Page',										//Объект генерирования страницы
	['cs\\DB',			'db'],						//Объект БД
	'_cs\\Storage',									//Объект Хранилищ
	'cs\\Config',									//Объект настроек
	'_cs\\Mail',									//Объект работы с почтой
	'_cs\\Key',										//Объект веменных ключей
	'cs\\User',										//Объект пользователя
	'cs\\Index'										//Объект, который управляет обработкой компонентов
]);
$Objects->__finish();								//Выгружает классы, отображает сгенерированный контент и корректно завершает работу
