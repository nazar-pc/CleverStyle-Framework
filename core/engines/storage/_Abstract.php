<?php
namespace cs\storage;
abstract class _Abstract {
	public		$connected	= false;
	protected	$base_url	= false;
	//Создание подключения
	//(хост [, пользователь [, пароль]])
	abstract function __construct ($base_url, $host, $user = '', $password = '');
	abstract function get_list ($dir, $mask = false, $mode='f', $with_path = false, $subfolders = false, $sort = false, $exclusion = false);
	abstract function file_get_contents ($filename, $flags = 0);
	abstract function file_put_contents ($filename, $data, $flags = 0);
	abstract function copy ($source, $dest);
	abstract function unlink ($filename);
	abstract function file_exists ($filename);
	abstract function move_uploaded_file ($filename, $destination);
	abstract function rename ($oldname, $newname);
	abstract function mkdir ($pathname, $mode = 0777, $recursive = false);
	abstract function rmdir ($dirname);
	abstract function url_by_source ($source);
	abstract function source_by_url ($url);
	abstract function is_file ($filename);
	abstract function is_dir ($filename);
	function base_url () {
		return $this->base_url;
	}
	/**
	 * Cloning restriction
	 *
	 * @final
	 */
	final function __clone () {}
}