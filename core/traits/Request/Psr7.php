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

trait Psr7 {
	/**
	 * Initialize request from PSR-7 request object
	 *
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 *
	 * @throws \cs\ExitException
	 */
	function from_psr7 ($request) {
		$this->from_psr7_server($request);
		$this->from_psr7_query($request);
		$this->from_psr7_data_and_files($request);
		$this->init_route();
	}
	/**
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 */
	protected function from_psr7_server ($request) {
		$uri          = $request->getUri();
		$this->method = $request->getMethod();
		$this->host   = $uri->getHost();
		$this->scheme = $uri->getScheme();
		$this->secure = $this->scheme == 'https';
		if (
			(!$this->secure && $uri->getPort() != 80) ||
			($this->secure && $uri->getPort() != 443)
		) {
			$this->host .= ':'.$uri->getPort();
		}
		$this->protocol     = 'HTTP/'.$request->getProtocolVersion();
		$this->path         = $uri->getPath();
		$this->query_string = $uri->getQuery();
		/** @noinspection NestedTernaryOperatorInspection */
		$this->uri         = $this->path.($this->query_string ? "?$this->query_string" : '') ?: '/';
		$this->remote_addr = @$request->getServerParams()['REMOTE_ADDR'] ?: '127.0.0.1';
		$this->ip          = $this->ip(
			[
				'HTTP_X_FORWARDED_FOR'     => $request->getHeaderLine('x-forwarded-for'),
				'HTTP_CLIENT_IP'           => $request->getHeaderLine('client-ip'),
				'HTTP_X_FORWARDED'         => $request->getHeaderLine('x-forwarded'),
				'HTTP_X_CLUSTER_CLIENT_IP' => $request->getHeaderLine('x-cluster-client-ip'),
				'HTTP_FORWARDED_FOR'       => $request->getHeaderLine('forwarded-for'),
				'HTTP_FORWARDED'           => $request->getHeaderLine('forwarded')
			]
		);
	}
	/**
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 */
	protected function from_psr7_query ($request) {
		$this->query = $request->getQueryParams();
	}
	/**
	 * @todo Implement custom stream wrapper for files and data in general in order to avoid data duplication
	 *
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 *
	 * @throws \cs\ExitException
	 */
	protected function from_psr7_data_and_files ($request) {
		$data         = [];
		$data_stream  = null;
		$content_type = $this->header('content-type');
		$body_stream  = $request->getBody();
		if (preg_match('#^application/([^+\s]+\+)?json#', $content_type)) {
			$data = _json_decode((string)$body_stream) ?: [];
		} elseif (strpos($content_type, 'application/x-www-form-urlencoded') === 0) {
			@parse_str((string)$body_stream, $data);
		} else {
			try {
				$position = $body_stream->tell();
				$body_stream->rewind();
				$data_stream = fopen('php://temp', 'w+b');
				while (!$body_stream->eof()) {
					fwrite($data_stream, $body_stream->read(1024));
				}
				$body_stream->seek($position);
			} catch (Exception $e) {
				// Do nothing
			}
		}
		$this->init_data_and_files(
			$data,
			$this->from_psr7_files_internal(
				$request->getUploadedFiles()
			),
			$data_stream
		);
	}
	/**
	 * @param array|\Psr\Http\Message\UploadedFileInterface $files
	 *
	 * @return array|\Psr\Http\Message\UploadedFileInterface
	 */
	protected function from_psr7_files_internal ($files) {
		if (is_array($files)) {
			foreach ($files as $field => &$file) {
				$file = $this->from_psr7_files_internal($file);
				if (!$file) {
					unset($files[$field]);
				}
			}
			return $files;
		}
		try {
			$source_file_stream = $files->getStream();
			$position           = $source_file_stream->tell();
			$source_file_stream->rewind();
			$file_stream = fopen('php://temp', 'w+b');
			while (!$source_file_stream->eof()) {
				fwrite($file_stream, $source_file_stream->read(1024));
			}
			$source_file_stream->seek($position);
		} catch (Exception $e) {
			return [];
		}
		return [
			'name'   => $files->getClientFilename(),
			'type'   => $files->getClientMediaType(),
			'size'   => $files->getSize(),
			'stream' => $file_stream,
			'error'  => $files->getError()
		];
	}
}
