<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Storage;
abstract class _Abstract {
	protected $connected = false;
	protected $base_url  = '';
	/**
	 * Connecting to the Storage
	 *
	 * @param string $base_url
	 * @param string $host
	 * @param string $user
	 * @param string $password
	 */
	abstract function __construct ($base_url, $host, $user = '', $password = '');
	/**
	 * Function for getting content of a directory
	 *
	 * @abstract
	 *
	 * @see get_files_list()
	 *
	 * @param    string        $dir
	 * @param    bool|string   $mask
	 * @param    string        $mode
	 * @param    bool|string   $prefix_path
	 * @param    bool          $subfolders
	 * @param    bool          $sort
	 * @param    bool|string   $exclusion
	 * @param    bool          $system_files
	 * @param    callable|null $apply
	 * @param    int|null      $limit
	 *
	 * @return    array|false
	 */
	abstract function get_files_list (
		$dir,
		$mask = false,
		$mode = 'f',
		$prefix_path = false,
		$subfolders = false,
		$sort = false,
		$exclusion = false,
		$system_files = false,
		$apply = null,
		$limit = null
	);
	/**
	 * Reads entire file into an array
	 *
	 * @abstract
	 *
	 * @see file()
	 *
	 * @param string   $filename
	 * @param int|null $flags
	 *
	 * @return array|false
	 */
	abstract function file ($filename, $flags = null);
	/**
	 * Reads entire file into a string
	 *
	 * @abstract
	 *
	 * @see file_get_contents()
	 *
	 * @param string   $filename
	 * @param int|null $flags
	 *
	 * @return false|string
	 */
	abstract function file_get_contents ($filename, $flags = null);
	/**
	 * Write a string to a file
	 *
	 * @abstract
	 *
	 * @see file_put_contents()
	 *
	 * @param string   $filename
	 * @param string   $data
	 * @param int|null $flags
	 *
	 * @return false|int
	 */
	abstract function file_put_contents ($filename, $data, $flags = null);
	/**
	 * Copies file
	 *
	 * @abstract
	 *
	 * @see copy()
	 *
	 * @param string $source
	 * @param string $dest
	 *
	 * @return bool
	 */
	abstract function copy ($source, $dest);
	/**
	 * Deletes a file
	 *
	 * @abstract
	 *
	 * @see unlink()
	 *
	 * @param string $filename
	 *
	 * @return bool
	 */
	abstract function unlink ($filename);
	/**
	 * Checks whether a file or directory exists
	 *
	 * @abstract
	 *
	 * @see file_exists()
	 *
	 * @param string $filename
	 *
	 * @return bool
	 */
	abstract function file_exists ($filename);
	/**
	 * Moves an uploaded file to a new location
	 *
	 * @abstract
	 *
	 * @see move_uploaded_file()
	 *
	 * @param string $filename
	 * @param string $destination
	 *
	 * @return bool
	 */
	abstract function move_uploaded_file ($filename, $destination);
	/**
	 * Renames a file or directory
	 *
	 * @abstract
	 *
	 * @see rename()
	 *
	 * @param string $oldname
	 * @param string $newname
	 *
	 * @return bool
	 */
	abstract function rename ($oldname, $newname);
	/**
	 * Attempts to create the directory specified by pathname.
	 *
	 * @abstract
	 *
	 * @see mkdir()
	 *
	 * @param string $pathname
	 * @param int    $mode
	 * @param bool   $recursive
	 *
	 * @return bool
	 */
	abstract function mkdir ($pathname, $mode = 0777, $recursive = false);
	/**
	 * Removes directory
	 *
	 * @abstract
	 *
	 * @see rmdir()
	 *
	 * @param string $dirname
	 *
	 * @return bool
	 */
	abstract function rmdir ($dirname);
	/**
	 * Tells whether the filename is a regular file
	 *
	 * @abstract
	 *
	 * @see is_file()
	 *
	 * @param string $filename
	 *
	 * @return bool
	 */
	abstract function is_file ($filename);
	/**
	 * Tells whether the filename is a directory
	 *
	 * @abstract
	 *
	 * @see is_dir()
	 *
	 * @param string $filename
	 *
	 * @return bool
	 */
	abstract function is_dir ($filename);
	/**
	 * Get file url by it's destination in file system
	 *
	 * @abstract
	 *
	 * @see url_by_source()
	 *
	 * @param string $source
	 *
	 * @return false|string
	 */
	abstract function url_by_source ($source);
	/**
	 * Get file destination in file system by it's url
	 *
	 * @abstract
	 *
	 * @see source_by_url()
	 *
	 * @param string $url
	 *
	 * @return false|string
	 */
	abstract function source_by_url ($url);
	/**
	 * Return base url of storage
	 *
	 * @return string
	 */
	function base_url () {
		return $this->base_url;
	}
	/**
	 * Connection state
	 *
	 * @return bool
	 */
	function connected () {
		return $this->connected;
	}
	/**
	 * Cloning restriction
	 *
	 * @final
	 */
	final function __clone () {
	}
}
