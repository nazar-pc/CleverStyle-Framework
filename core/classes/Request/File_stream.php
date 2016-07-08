<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Request;
use
	cs\Request;

/**
 * Stream wrapper created in order to be used as virtual stream which actually represents uploaded file, which is only available in form of stream, but we
 * sometimes need to handle it like regular file
 *
 * Usage:
 * * `fopen('request-file:///file', 'r')`
 * * `fopen('request-file:///file/0', 'r')`
 *
 * `/file` basically represents `cs\Request::files['file']` and `/file/0` `cs\Request::files['file'][0]`
 */
class File_stream {
	/**
	 * @var resource
	 */
	protected $stream;
	/**
	 * @var int
	 */
	protected $position;

	function stream_open ($path, $mode) {
		if ($mode != 'r' && $mode != 'rb') {
			return false;
		}
		$files = Request::instance()->files;
		foreach (explode('/', explode(':///', $path)[1]) as $file_path) {
			if (!isset($files[$file_path])) {
				return false;
			}
			$files = $files[$file_path];
		}
		$this->stream   = $files['stream'];
		$this->position = 0;
		return true;
	}
	/**
	 * @param int $length
	 *
	 * @return false|string
	 */
	function stream_read ($length) {
		fseek($this->stream, $this->position);
		$bytes          = fread($this->stream, $length);
		$this->position += strlen($bytes);
		return $bytes;
	}
	/**
	 * @return false|int
	 */
	function stream_tell () {
		return $this->position;
	}
	/**
	 * @return bool
	 */
	function stream_eof () {
		fseek($this->stream, $this->position);
		return feof($this->stream);
	}
	/**
	 * @param int $offset
	 * @param int $whence
	 *
	 * @return int
	 */
	function stream_seek ($offset, $whence = SEEK_SET) {
		fseek($this->stream, $this->position);
		$result         = fseek($this->stream, $offset, $whence);
		$this->position = ftell($this->stream);
		return $result;
	}
	/**
	 * @return array
	 */
	function stream_stat () {
		return fstat($this->stream);
	}
}
