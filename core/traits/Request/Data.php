<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Request;

trait Data {
	/**
	 * Data array, similar to `$_POST`
	 *
	 * @var array
	 */
	public $data;
	/**
	 * Data stream resource, similar to `fopen('php://input', 'br')`
	 *
	 * Make sure you're controlling position in stream where you read something, if code in some other place might seek on this stream
	 *
	 * @var null|resource
	 */
	public $data_stream;
	/**
	 * @param array                $data        Typically `$_POST`
	 * @param null|resource|string $data_stream String, like `php://input` or resource, like `fopen('php://input', 'br')`
	 */
	function init_data ($data = [], $data_stream = null) {
		if (is_resource($this->data_stream)) {
			fclose($this->data_stream);
		}
		$this->data        = $data;
		$this->data_stream = is_string($data_stream) ? fopen($data_stream, 'br') : $data_stream;
	}
}
