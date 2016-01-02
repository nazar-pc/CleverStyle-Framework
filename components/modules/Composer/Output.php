<?php
/**
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Composer;
use
	Symfony\Component\Console\Output\Output as Symfony_output;

class Output extends Symfony_output {
	/**
	 * @var string
	 */
	protected $buffer = '';
	/**
	 * @var resource
	 */
	protected $stream;
	/**
	 * Set stream where content should be written to
	 *
	 * @param resource $stream Resource, for instance, form `fopen(.., 'w')` call
	 */
	function set_stream ($stream) {
		$this->stream = $stream;
	}
	/**
	 * @return string
	 */
	function fetch () {
		$content      = $this->buffer;
		$this->buffer = '';
		return $content;
	}
	/**
	 * @param string $message
	 * @param bool   $newline
	 */
	protected function doWrite ($message, $newline) {
		if ($newline) {
			$message .= "\n";
		}
		fwrite($this->stream, $message);
		$this->buffer .= $message;
	}
}
