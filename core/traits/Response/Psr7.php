<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Response;
use
	Exception;

trait Psr7 {
	/**
	 * Provides output to PSR-7 response object
	 *
	 * @param \Psr\Http\Message\ResponseInterface $response
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	function output_to_psr7 ($response) {
		$this->to_psr7_body($response);
		$response = $this->to_psr7_headers($response);
		/** @noinspection ExceptionsAnnotatingAndHandlingInspection */
		return $response
			->withProtocolVersion(explode('/', $this->protocol, 2)[1])
			->withStatus($this->code);
	}
	/**
	 * @param \Psr\Http\Message\ResponseInterface $response
	 */
	protected function to_psr7_body ($response) {
		$body = $response->getBody();
		try {
			if (is_resource($this->body_stream)) {
				$position = ftell($this->body_stream);
				rewind($this->body_stream);
				while (!feof($this->body_stream)) {
					$body->write(fread($this->body_stream, 1024));
				}
				fseek($this->body_stream, $position);
			} else {
				$body->write($this->body);
			}
		} catch (Exception $e) {
			// Do nothing
		}
	}
	/**
	 * @param \Psr\Http\Message\ResponseInterface $response
	 *
	 * @return \Psr\Http\Message\ResponseInterface $response
	 */
	protected function to_psr7_headers ($response) {
		foreach ($this->headers as $header => $values) {
			try {
				$response = $response->withHeader($header, $values);
			} catch (Exception $e) {
				// Do nothing
			}
		}
		return $response;
	}
}
