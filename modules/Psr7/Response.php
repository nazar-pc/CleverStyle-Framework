<?php
/**
 * @package    Psr7
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license    0BSD
 */
namespace cs\modules\Psr7;
use
	cs\Request as System_request,
	cs\Response as System_response,
	Exception;

class Response {
	/**
	 * Provides output to PSR-7 response object
	 *
	 * @param \Psr\Http\Message\ResponseInterface $Psr7_response
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public static function output_to_psr7 ($Psr7_response) {
		$System_response = System_response::instance();
		self::to_psr7_body($System_response, $Psr7_response);
		$Psr7_response = self::to_psr7_headers($System_response, $Psr7_response);
		/** @noinspection ExceptionsAnnotatingAndHandlingInspection */
		return $Psr7_response
			->withProtocolVersion(explode('/', System_request::instance()->protocol, 2)[1])
			->withStatus($System_response->code);
	}
	/**
	 * @param System_response                     $System_response
	 * @param \Psr\Http\Message\ResponseInterface $Psr7_response
	 */
	protected static function to_psr7_body ($System_response, $Psr7_response) {
		$body = $Psr7_response->getBody();
		try {
			if (is_resource($System_response->body_stream)) {
				$position = ftell($System_response->body_stream);
				rewind($System_response->body_stream);
				while (!feof($System_response->body_stream)) {
					$body->write(fread($System_response->body_stream, 1024));
				}
				fseek($System_response->body_stream, $position);
			} else {
				$body->write($System_response->body);
			}
		} catch (Exception $e) {
			// Do nothing
		}
	}
	/**
	 * @param System_response                     $System_response
	 * @param \Psr\Http\Message\ResponseInterface $Psr7_response
	 *
	 * @return \Psr\Http\Message\ResponseInterface $Psr7_response
	 */
	protected static function to_psr7_headers ($System_response, $Psr7_response) {
		foreach ($System_response->headers as $header => $values) {
			try {
				$Psr7_response = $Psr7_response->withHeader($header, $values);
			} catch (Exception $e) {
				// Do nothing
			}
		}
		return $Psr7_response;
	}
}
