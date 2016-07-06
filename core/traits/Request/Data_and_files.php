<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Request;
use
	UnexpectedValueException,
	cs\ExitException,
	nazarpc\Stream_slicer;

trait Data_and_files {
	/**
	 * Data array, similar to `$_POST`
	 *
	 * @var array
	 */
	public $data;
	/**
	 * Normalized files array
	 *
	 * Each file item can be either single file or array of files (in contrast with native PHP arrays where each field like `name` become an array) with keys
	 * `name`, `type`, `size`, `tmp_name`, `stream` and `error`
	 *
	 * `name`, `type`, `size` and `error` keys are similar to native PHP fields in `$_FILES`; `tmp_name` might not be temporary file, but file descriptor
	 * wrapper like `request-file:///file` instead and `stream` is resource like obtained with `fopen('/tmp/xyz', 'rb')`
	 *
	 * @var array[]
	 */
	public $files;
	/**
	 * Data stream resource, similar to `fopen('php://input', 'rb')`
	 *
	 * Make sure you're controlling position in stream where you read something, if code in some other place might seek on this stream
	 *
	 * Stream is read-only
	 *
	 * @var null|resource
	 */
	public $data_stream;
	/**
	 * `$this->init_server()` assumed to be called already
	 *
	 * @param array                $data        Typically `$_POST`
	 * @param array[]              $files       Typically `$_FILES`; might be like native PHP array `$_FILES` or normalized; each file item MUST contain keys
	 *                                          `name`, `type`, `size`, `error` and at least one of `tmp_name` or `stream`
	 * @param null|resource|string $data_stream String, like `php://input` or resource, like `fopen('php://input', 'rb')` with request body, will be parsed for
	 *                                          data and files if necessary
	 * @param bool                 $copy_stream Sometimes data stream can only being read once (like most of times with `php://input`), so it is necessary to
	 *                                          copy it and store its contents for longer period of time
	 *
	 * @throws ExitException
	 */
	function init_data_and_files ($data = [], $files = [], $data_stream = null, $copy_stream = true) {
		if (is_resource($this->data_stream)) {
			fclose($this->data_stream);
		}
		$this->data        = $data;
		$this->files       = $this->normalize_files($files);
		$this->data_stream = null;
		if (in_array($this->method, ['GET', 'HEAD', 'OPTIONS'])) {
			return;
		}
		$data_stream = is_string($data_stream) ? fopen($data_stream, 'rb') : $data_stream;
		if (is_resource($data_stream)) {
			rewind($data_stream);
			if ($copy_stream) {
				$this->data_stream = fopen('php://temp', 'w+b');
				stream_copy_to_stream($data_stream, $this->data_stream);
				fclose($data_stream);
			} else {
				$this->data_stream = $data_stream;
			}
		}
		/**
		 * If we don't appear to have any data or files detected - probably, we need to parse request ourselves
		 */
		if (!$this->data && !$this->files && is_resource($this->data_stream)) {
			$this->parse_data_stream();
		}
		// Hack: for compatibility we'll override $_POST since it might be filled during parsing
		$_POST = $this->data;
	}
	/**
	 * Get data item by name
	 *
	 * @param string[]|string[][] $name
	 *
	 * @return mixed|mixed[]|null Data items (or associative array of data items) if exists or `null` otherwise (in case if `$name` is an array even one
	 *                             missing key will cause the whole thing to fail)
	 */
	function data (...$name) {
		if (count($name) === 1) {
			$name = $name[0];
		}
		/**
		 * @var string|string[] $name
		 */
		if (is_array($name)) {
			$result = [];
			foreach ($name as &$n) {
				if (!array_key_exists($n, $this->data)) {
					return null;
				}
				$result[$n] = $this->data[$n];
			}
			return $result;
		}
		/** @noinspection OffsetOperationsInspection */
		return @$this->data[$name];
	}
	/**
	 * Get file item by name
	 *
	 * @param string $name
	 *
	 * @return array|null File item if exists or `null` otherwise
	 */
	function files ($name) {
		return @$this->files[$name];
	}
	/**
	 * @param array[] $files
	 * @param string  $file_path
	 *
	 * @return array[]
	 */
	protected function normalize_files ($files, $file_path = '') {
		if (!isset($files['name'])) {
			foreach ($files as $field => &$file) {
				$file = $this->normalize_files($file, "$file_path/$field");
			}
			return $files;
		}
		if (is_array($files['name'])) {
			$result = [];
			foreach (array_keys($files['name']) as $index) {
				$result[] = $this->normalize_file(
					[
						'name'     => $files['name'][$index],
						'type'     => $files['type'][$index],
						'size'     => $files['size'][$index],
						'tmp_name' => @$files['tmp_name'][$index],
						'stream'   => @$files['stream'][$index],
						'error'    => $files['error'][$index]
					],
					"$file_path/$index"
				);
			}
			return $result;
		} else {
			return $this->normalize_file($files, $file_path);
		}
	}
	/**
	 * @param array  $file
	 * @param string $file_path
	 *
	 * @return array
	 */
	protected function normalize_file ($file, $file_path) {
		$file += [
			'tmp_name' => null,
			'stream'   => null
		];
		if (isset($file['tmp_name']) && $file['stream'] === null) {
			$file['stream'] = fopen($file['tmp_name'], 'rb');
		}
		if (isset($file['stream']) && !$file['tmp_name']) {
			$file['tmp_name'] = "request-file://$file_path";
		}
		if ($file['tmp_name'] === null && $file['stream'] === null) {
			$file['error'] = UPLOAD_ERR_NO_FILE;
		}
		return $file;
	}
	/**
	 * Parsing request body for following Content-Type: `application/json`, `application/x-www-form-urlencoded` and `multipart/form-data`
	 *
	 * @throws ExitException
	 */
	protected function parse_data_stream () {
		$content_type = $this->header('content-type');
		rewind($this->data_stream);
		/**
		 * application/json
		 */
		if (preg_match('#^application/([^+\s]+\+)?json#', $content_type)) {
			$this->data = _json_decode(stream_get_contents($this->data_stream)) ?: [];
			return;
		}
		/**
		 * application/x-www-form-urlencoded
		 */
		if (strpos($content_type, 'application/x-www-form-urlencoded') === 0) {
			@parse_str(stream_get_contents($this->data_stream), $this->data);
			return;
		}
		/**
		 * multipart/form-data
		 */
		if (preg_match('#multipart/form-data;.*boundary="?([^;"]{1,70})(?:"|;|$)#Ui', $content_type, $matches)) {
			try {
				$parts = $this->parse_multipart_into_parts($this->data_stream, trim($matches[1])) ?: [];
				list($this->data, $files) = $this->parse_multipart_analyze_parts($this->data_stream, $parts);
				$this->files = $this->normalize_files($files);
			} catch (UnexpectedValueException $e) {
				// Do nothing, if parsing failed then we'll just leave `::$data` and `::$files` empty
			}
		}
	}
	/**
	 * Parse content stream
	 *
	 * @param resource $stream
	 * @param string   $boundary
	 *
	 * @return array[]|false
	 *
	 * @throws UnexpectedValueException
	 * @throws ExitException
	 */
	protected function parse_multipart_into_parts ($stream, $boundary) {
		$parts    = [];
		$crlf     = "\r\n";
		$position = 0;
		$body     = '';
		list($offset, $body) = $this->parse_multipart_find($stream, $body, "--$boundary$crlf");
		/**
		 * strlen doesn't take into account trailing CRLF since we'll need it in loop below
		 */
		$position += $offset + strlen("--$boundary");
		$body = substr($body, strlen("--$boundary"));
		/**
		 * Each part always starts with CRLF
		 */
		while (strpos($body, $crlf) === 0) {
			$position += 2;
			$body = substr($body, 2);
			$part = [
				'headers' => [
					'offset' => $position,
					'size'   => 0
				],
				'body'    => [
					'offset' => 0,
					'size'   => 0
				]
			];
			if (strpos($body, $crlf) === 0) {
				/**
				 * No headers
				 */
				$position += 2;
				$body = substr($body, 2);
			} else {
				/**
				 * Find headers end in order to determine size
				 */
				list($offset, $body) = $this->parse_multipart_find($stream, $body, $crlf.$crlf);
				$part['headers']['size'] = $offset;
				$position += $offset + 4;
				$body = substr($body, 4);
			}
			$part['body']['offset'] = $position;
			/**
			 * Find body end in order to determine its size
			 */
			list($offset, $body) = $this->parse_multipart_find($stream, $body, "$crlf--$boundary");
			$part['body']['size'] = $offset;
			$position += $offset + strlen("$crlf--$boundary");
			$body = substr($body, strlen("$crlf--$boundary"));
			if ($part['headers']['size']) {
				$parts[] = $part;
			}
		}
		/**
		 * Last boundary after all parts ends with '--' and we don't care what rubbish happens after it
		 */
		$post_max_size = $this->post_max_size();
		if (strpos($body, '--') !== 0) {
			return false;
		}
		/**
		 * Check whether body size is bigger than allowed limit
		 */
		if ($position + strlen($body) > $post_max_size) {
			throw new ExitException(413);
		}
		return $parts;
	}
	/**
	 * @param resource $stream
	 * @param array[]  $parts
	 *
	 * @return array[]
	 */
	protected function parse_multipart_analyze_parts ($stream, $parts) {
		$data  = [];
		$files = [];
		foreach ($parts as $part) {
			$headers = $this->parse_multipart_headers(
				stream_get_contents($stream, $part['headers']['size'], $part['headers']['offset'])
			);
			if (
				!isset($headers['content-disposition'][0], $headers['content-disposition']['name']) ||
				$headers['content-disposition'][0] != 'form-data'
			) {
				continue;
			}
			$name = $headers['content-disposition']['name'];
			if (isset($headers['content-disposition']['filename'])) {
				$file = [
					'name'   => $headers['content-disposition']['filename'],
					'type'   => @$headers['content-type'] ?: 'application/octet-stream',
					'size'   => $part['body']['size'],
					'stream' => Stream_slicer::slice($stream, $part['body']['offset'], $part['body']['size']),
					'error'  => UPLOAD_ERR_OK
				];
				if ($file['name'] === '') {
					$file['type']   = '';
					$file['stream'] = null;
					$file['error']  = UPLOAD_ERR_NO_FILE;
				} elseif ($file['size'] > $this->upload_max_file_size()) {
					$file['stream'] = null;
					$file['error']  = UPLOAD_ERR_INI_SIZE;
				}
				$this->parse_multipart_set_target($files, $name, $file);
			} else {
				if ($part['body']['size'] == 0) {
					$this->parse_multipart_set_target($data, $name, '');
				} else {
					$this->parse_multipart_set_target(
						$data,
						$name,
						stream_get_contents($stream, $part['body']['size'], $part['body']['offset'])
					);
				}
			}
		}
		return [$data, $files];
	}
	/**
	 * @return int
	 */
	protected function post_max_size () {
		$size = ini_get('post_max_size') ?: ini_get('hhvm.server.max_post_size');
		return $this->convert_size_to_bytes($size);
	}
	/**
	 * @return int
	 */
	protected function upload_max_file_size () {
		$size = ini_get('upload_max_filesize') ?: ini_get('hhvm.server.upload.upload_max_file_size');
		return $this->convert_size_to_bytes($size);
	}
	/**
	 * @param int|string $size
	 *
	 * @return int
	 */
	protected function convert_size_to_bytes ($size) {
		switch (strtolower(substr($size, -1))) {
			case 'g';
				$size = (int)$size * 1024;
			case 'm';
				$size = (int)$size * 1024;
			case 'k';
				$size = (int)$size * 1024;
		}
		return (int)$size ?: PHP_INT_MAX;
	}
	/**
	 * @param resource $stream
	 * @param string   $next_data
	 * @param string   $target
	 *
	 * @return array
	 *
	 * @throws UnexpectedValueException
	 */
	protected function parse_multipart_find ($stream, $next_data, $target) {
		$offset    = 0;
		$prev_data = '';
		while (($found = strpos($prev_data.$next_data, $target)) === false) {
			if (feof($stream)) {
				throw new UnexpectedValueException;
			}
			if ($prev_data) {
				$offset += strlen($prev_data);
			}
			$prev_data = $next_data;
			$next_data = fread($stream, 1024);
		}
		$offset += $found;
		/**
		 * Read some more bytes so that we'll always have some remainder in place, since empty remainder might cause problems with `strpos()` call later
		 */
		$remainder = substr($prev_data.$next_data, $found).(fread($stream, 1024) ?: '');
		return [$offset, $remainder];
	}
	/**
	 * @param string $content
	 *
	 * @return array
	 */
	protected function parse_multipart_headers ($content) {
		$headers = [];
		foreach (explode("\r\n", $content) as $header) {
			list($name, $value) = explode(':', $header, 2);
			if (!preg_match_all('/(.+)(?:="?([^"]*)"?)?(?:;\s|$)/U', $value, $matches)) {
				continue;
			}
			$name           = strtolower($name);
			$headers[$name] = [];
			foreach (array_keys($matches[1]) as $index) {
				if (isset($headers[$name][0]) || strlen($matches[2][$index])) {
					$headers[$name][trim($matches[1][$index])] = urldecode(trim($matches[2][$index]));
				} else {
					$headers[$name][] = trim($matches[1][$index]);
				}
			}
			if (count($headers[$name]) == 1) {
				$headers[$name] = @$headers[$name][0];
			}
		}
		return $headers;
	}
	/**
	 * @param array        $source
	 * @param string       $name
	 * @param array|string $value
	 */
	protected function parse_multipart_set_target (&$source, $name, $value) {
		preg_match_all('/(?:^|\[)([^\[\]]*)\]?/', $name, $matches);
		if ($matches[1][0] === '') {
			return;
		}
		foreach ($matches[1] as $component) {
			if (!strlen($component)) {
				$source = &$source[];
			} else {
				if (!isset($source[$component])) {
					$source[$component] = [];
				}
				$source = &$source[$component];
			}
		}
		$source = $value;
	}
}
