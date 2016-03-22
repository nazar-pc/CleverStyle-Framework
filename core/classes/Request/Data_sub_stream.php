<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Request;
use
	cs\Request;

/**
 * Stream wrapper created in order to be used as virtual stream which actually represents segment of request data stream, this way we avoid data duplication
 *
 * Usage: `fopen('request-data://offset:size', 'r')`
 *
 * `offset` and `size` are used to specify segment of data within request data stream, only `r` mode is supported
 */
class Data_sub_stream {
	/**
	 * Offset of current file inside parent input stream
	 *
	 * @var int
	 */
	protected $offset;
	/**
	 * Size of current file in bytes
	 *
	 * @var int
	 */
	protected $size;
	/**
	 * @var array
	 */
	protected $stat = [];
	/**
	 * Current position of a stream
	 *
	 * @var int
	 */
	protected $position = 0;
	/**
	 * @var resource
	 */
	protected $data_stream;

	function stream_open ($path, $mode) {
		if ($mode != 'r' && $mode != 'rb') {
			return false;
		}
		$this->data_stream = Request::instance()->data_stream;
		list($this->offset, $this->size) = explode(':', explode('://', $path)[1]);
		$stat     = &$this->stat;
		$stat[0]  = $stat['dev'] = 0;
		$stat[1]  = $stat['ino'] = 0;
		$stat[2]  = $stat['mode'] = 0;
		$stat[3]  = $stat['nlink'] = 0;
		$stat[4]  = $stat['uid'] = 0;
		$stat[5]  = $stat['gid'] = 0;
		$stat[6]  = $stat['rdev'] = 0;
		$stat[7]  = $stat['size'] = $this->size;
		$time     = time();
		$stat[8]  = $stat['atime'] = $time;
		$stat[9]  = $stat['mtime'] = $time;
		$stat[10] = $stat['ctime'] = $time;
		$stat[11] = $stat['blksize'] = -1;
		$stat[12] = $stat['blocks'] = -1;
		return true;
	}
	/**
	 * @param int $length
	 *
	 * @return false|string
	 */
	function stream_read ($length) {
		if ($this->stream_eof()) {
			return false;
		}
		return $this->stream(
			function ($stream) use ($length) {
				fseek($stream, $this->offset + $this->position);
				/**
				 * Avoid going out of file boundary
				 */
				$to_read = min($length, $this->size - $this->position);
				$this->position += $to_read;
				return fread($stream, $to_read);
			}
		);
	}
	/**
	 * @param callable $callback
	 *
	 * @return mixed
	 */
	protected function stream ($callback) {
		$position = ftell($this->data_stream);
		$result   = $callback($this->data_stream);
		fseek($this->data_stream, $position);
		return $result;
	}
	/**
	 * @return int
	 */
	function stream_tell () {
		return $this->position;
	}
	/**
	 * @return bool
	 */
	function stream_eof () {
		return $this->position == $this->size;
	}
	/**
	 * @param int $offset
	 * @param int $whence
	 *
	 * @return int
	 */
	function stream_seek ($offset, $whence = SEEK_SET) {
		if ($whence == SEEK_SET && $offset >= 0 && $offset < $this->size) {
			$this->position = $offset;
			return 0;
		}
		$position = $this->position + $offset;
		if ($whence == SEEK_CUR && $offset >= 0 && $position < $this->size) {
			$this->position = $position;
			return 0;
		}
		$position = $this->size + $offset;
		if ($whence == SEEK_END && $offset <= 0 && $position >= 0) {
			$this->position = $position;
			return 0;
		}
		return -1;
	}
	/**
	 * @return array
	 */
	function stream_stat () {
		return $this->stat;
	}
}
