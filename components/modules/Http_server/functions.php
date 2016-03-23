<?php
/**
 * @package   Http server
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
use
	cs\Response;

/**
 * Get or Set the HTTP response code (similar to built-in function)
 *
 * @deprecated Use `cs\Request::$code` instead
 * @todo       Remove in 4.x
 *
 * @param int $response_code The optional response_code will set the response code.
 *
 * @return int The current response code. By default the return value is int(200).
 */
function _http_response_code ($response_code) {
	Response::instance()->code = $response_code;
}
