<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Request;
use
	Exception;

/**
 * Stream wrapper created in order to be used as virtual stream which actually represents PSR7 Psr\Http\Message\StreamInterface, but in form that can be used
 * in `fopen()`, this is all useful in order to avoid copying entire stream from PSR7 representation into regular PHP stream
 *
 * Usage: `fopen('request-psr7-data://', 'r')`
 */
class Psr7_data_stream {
	/**
	 * PSR7 request body stream is injected here
	 *
	 * @var \Psr\Http\Message\StreamInterface
	 */
	public static $stream;
	/**
	 * @var array
	 */
	protected $stat = [];

	function stream_open ($path, $mode) {
		if ($mode != 'r' && $mode != 'rb') {
			return false;
		}
		$stat     = &$this->stat;
		$stat[0]  = $stat['dev'] = 0;
		$stat[1]  = $stat['ino'] = 0;
		$stat[2]  = $stat['mode'] = 0;
		$stat[3]  = $stat['nlink'] = 0;
		$stat[4]  = $stat['uid'] = 0;
		$stat[5]  = $stat['gid'] = 0;
		$stat[6]  = $stat['rdev'] = 0;
		$stat[7]  = $stat['size'] = static::$stream->getSize();
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
		try {
			return static::$stream->read($length);
		} catch (Exception $e) {
			return false;
		}
	}
	/**
	 * @return false|int
	 */
	function stream_tell () {
		try {
			return static::$stream->tell();
		} catch (Exception $e) {
			return false;
		}
	}
	/**
	 * @return bool
	 */
	function stream_eof () {
		return static::$stream->eof();
	}
	/**
	 * @param int $offset
	 * @param int $whence
	 *
	 * @return int
	 */
	function stream_seek ($offset, $whence = SEEK_SET) {
		try {
			return static::$stream->seek($offset, $whence);
		} catch (Exception $e) {
			return -1;
		}
	}
	/**
	 * @return array
	 */
	function stream_stat () {
		return $this->stat;
	}
}
