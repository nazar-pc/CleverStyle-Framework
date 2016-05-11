<?php
/**
 * @package   Steam slicer
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace nazarpc;

class Stream_slicer {
	/**
	 * @var resource
	 */
	protected $stream;
	/**
	 * Offset of current file inside parent input stream in bytes
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
	 * @var int[]
	 */
	protected $stat = [
		'dev'     => 0,
		'ino'     => 0,
		'mode'    => 0,
		'nlink'   => 0,
		'uid'     => 0,
		'gid'     => 0,
		'rdev'    => 0,
		'size'    => 0,
		'atime'   => 0,
		'mtime'   => 0,
		'ctime'   => 0,
		'blksize' => -1,
		'blocks'  => -1
	];
	/**
	 * Current position of a stream
	 *
	 * @var int
	 */
	protected $position = 0;
	/**
	 * @var bool
	 */
	protected static $registered = false;
	/**
	 * @var array
	 */
	protected static $tmp;
	/**
	 * Return stream slice
	 *
	 * @param resource $stream Seekable stream
	 * @param int      $offset Offset in bytes relatively to the beginning of the stream
	 * @param int      $size   Size of slice in bytes relatively to `$offset`
	 *
	 * @return false|resource
	 */
	static function slice ($stream, $offset, $size) {
		if (!is_resource($stream) || $offset < 0 || $size < 0) {
			return false;
		}
		if (!self::$registered) {
			stream_wrapper_register('stream-slicer', self::class);
			self::$registered = true;
		}
		self::$tmp = [$stream, (int)$offset, (int)$size];
		return fopen('stream-slicer://', 'r');
	}
	/**
	 * @return bool
	 */
	function stream_open () {
		if (!self::$tmp) {
			return false;
		}
		list($this->stream, $this->offset, $this->size) = self::$tmp;
		self::$tmp           = null;
		$this->stat['size']  = $this->size;
		$this->stat['atime'] = $this->stat['mtime'] = $this->stat['ctime'] = time();
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
		/**
		 * Avoid going out of file boundary
		 */
		$length   = min($length, $this->size - $this->position);
		$position = $this->position;
		$this->position += $length;
		return stream_get_contents($this->stream, $length, $this->offset + $position);
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
	 * @return bool
	 */
	function stream_seek ($offset, $whence = SEEK_SET) {
		if ($whence == SEEK_SET && $offset >= 0 && $offset < $this->size) {
			$this->position = $offset;
			return true;
		}
		$position = $this->position + $offset;
		if ($whence == SEEK_CUR && $offset >= 0 && $position < $this->size) {
			$this->position = $position;
			return true;
		}
		$position = $this->size + $offset;
		if ($whence == SEEK_END && $offset <= 0 && $position >= 0) {
			$this->position = $position;
			return true;
		}
		return false;
	}
	/**
	 * @return int[]
	 */
	function stream_stat () {
		/** @noinspection AdditionOperationOnArraysInspection */
		return $this->stat + array_values($this->stat);
	}
}
