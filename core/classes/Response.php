<?php
/**
 * @package CleverStyle Framework
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
namespace cs;
use function
	cli\err,
	cli\out;

class Response {
	use
		Singleton;
	/**
	 * HTTP status code
	 *
	 * @var int
	 */
	public $code;
	/**
	 * Headers are normalized to lowercase keys with hyphen as separator, for instance: `connection`, `referer`, `content-type`, `accept-language`
	 *
	 * Values might be strings in case of single value or array of strings in case of multiple values with the same field name
	 *
	 * @var string[][]
	 */
	public $headers = [];
	/**
	 * String body (is used instead of `$this->body_stream` in most cases, ignored if `$this->body_stream` is present)
	 *
	 * @var string
	 */
	public $body;
	/**
	 * Body in form of stream (might be used instead of `$this->body` in some cases, if present, `$this->body` is ignored)
	 *
	 * Stream is read/write
	 *
	 * @var resource
	 */
	public $body_stream;
	/**
	 * Initialize response object with specified data
	 *
	 * @param string               $body
	 * @param null|resource|string $body_stream String, like `php://temp` or resource, like `fopen('php://temp', 'a+b')`, if present, `$body` is ignored
	 * @param string[]|string[][]  $headers     Headers are normalized to lowercase keys with hyphen as separator, for instance: `connection`, `referer`,
	 *                                          `content-type`, `accept-language`; Values might be strings in case of single value or array of strings in case
	 *                                          of multiple values with the same field name
	 * @param int                  $code        HTTP status code
	 *
	 * @return Response
	 */
	public function init ($body = '', $body_stream = null, $headers = [], $code = 200) {
		$this->code    = $code;
		$this->headers = _array($headers);
		$this->body    = $body;
		if ($this->body_stream) {
			fclose($this->body_stream);
		}
		$this->body_stream = is_string($body_stream) ? fopen($body_stream, 'a+b') : $body_stream;
		return $this;
	}
	/**
	 * Initialize with typical default settings (headers `Content-Type`, `Vary` and `X-UA-Compatible`
	 *
	 * @return Response
	 */
	public function init_with_typical_default_settings () {
		return $this->init(
			'',
			null,
			[
				'content-type' => 'text/html; charset=utf-8',
				'vary'         => 'Accept-Language,User-Agent,Cookie'
			],
			200
		);
	}
	/**
	 * Set raw HTTP header
	 *
	 * @param string $field        Field
	 * @param string $value        Value, empty string will cause header removal
	 * @param bool   $replace      The optional replace parameter indicates whether the header should replace a previous similar header, or add a second header
	 *                             of the same type. By default it will replace
	 *
	 * @return Response
	 */
	public function header ($field, $value, $replace = true) {
		$field = strtolower($field);
		if ($value === '') {
			unset($this->headers[$field]);
		} elseif ($replace || !isset($this->headers[$field])) {
			$this->headers[$field] = [$value];
		} else {
			$this->headers[$field][] = $value;
		}
		return $this;
	}
	/**
	 * Make redirect to specified location
	 *
	 * @param string $location
	 * @param int    $code
	 *
	 * @return Response
	 */
	public function redirect ($location, $code = 302) {
		$this->header('location', $location);
		$this->code                 = $code;
		Page::instance()->interface = false;
		return $this;
	}
	/**
	 * Function for setting cookies, taking into account cookies prefix. Parameters like in system `setcookie()` function, but `$path`, `$domain` and `$secure`
	 * are skipped, they are detected automatically
	 *
	 * This function have side effect of setting cookie on `cs\Request` object
	 *
	 * @param string $name
	 * @param string $value
	 * @param int    $expire Unix timestamp in seconds
	 * @param bool   $httponly
	 *
	 * @return Response
	 */
	public function cookie ($name, $value, $expire = 0, $httponly = false) {
		$Config         = Config::instance();
		$Request        = Request::instance();
		$prefix         = $Config->core['cookie_prefix'];
		$cookie_domains = $Config->core['cookie_domain'];
		$domain         = $cookie_domains[$Request->mirror_index] ?? $cookie_domains[0];
		if ($value === '') {
			unset($Request->cookie[$name], $Request->cookie[$prefix.$name]);
		} else {
			$Request->cookie[$name]         = $value;
			$Request->cookie[$prefix.$name] = $value;
		}
		$header = [
			rawurlencode($prefix.$name).'='.rawurlencode($value),
			'path=/'
		];
		if ($expire || !$value) {
			$header[] = 'expires='.gmdate('D, d-M-Y H:i:s', $expire).' GMT';
		}
		if ($domain) {
			$header[] = "domain=$domain";
		}
		if ($Request->secure) {
			$header[] = 'secure';
		}
		if ($httponly) {
			$header[] = 'HttpOnly';
		}
		$this->header('set-cookie', implode('; ', $header), false);
		return $this;
	}
	/**
	 * Provides default output for all the response data using `header()`, `http_response_code()` and `echo` or `php://output`
	 */
	public function output_default () {
		ob_implicit_flush(true);
		if (Request::instance()->cli_path) {
			$this->output_default_cli();
		} else {
			$this->output_default_web();
		}
	}
	protected function output_default_cli () {
		if ($this->code >= 400 && $this->code <= 510) {
			err($this->body);
			exit($this->code % 256);
		}
		if (is_resource($this->body_stream)) {
			$position = ftell($this->body_stream);
			stream_copy_to_stream($this->body_stream, fopen('php://stdout', 'wb'));
			fseek($this->body_stream, $position);
		} else {
			out($this->body);
		}
	}
	protected function output_default_web () {
		foreach ($this->headers as $header => $value) {
			$header = ucwords($header, '-');
			foreach ($value as $v) {
				header("$header: $v", false);
			}
		}
		http_response_code($this->code);
		if ($this->code >= 300 && $this->code < 400) {
			return;
		}
		if (is_resource($this->body_stream)) {
			$position = ftell($this->body_stream);
			stream_copy_to_stream($this->body_stream, fopen('php://output', 'wb'));
			fseek($this->body_stream, $position);
		} else {
			echo $this->body;
		}
	}
}
