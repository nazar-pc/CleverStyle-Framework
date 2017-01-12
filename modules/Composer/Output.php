<?php
/**
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Composer;
use
	cs\Event,
	Symfony\Component\Console\Formatter\OutputFormatterInterface,
	Symfony\Component\Console\Output\Output as Symfony_output;

/**
 * Provides next events:
 *  Composer/update_progress
 *  [
 *   'message' => $message,     //Current message targeted for output
 *   'buffer'  => $this->buffer //Total output
 *  ]
 */
class Output extends Symfony_output {
	/**
	 * @var string
	 */
	protected $buffer = '';
	/**
	 * @var resource
	 */
	protected $stream;
	public function __construct ($verbosity = self::VERBOSITY_NORMAL, $decorated = false, OutputFormatterInterface $formatter = null) {
		$this->stream = fopen(STORAGE.'/Composer/last_execution.log', 'w');
		parent::__construct($verbosity, $decorated, $formatter);
	}
	/**
	 * @return string
	 */
	public function get_buffer () {
		return $this->buffer;
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
		Event::instance()->fire(
			'Composer/update_progress',
			[
				'message' => $message,
				'buffer'  => $this->buffer
			]
		);
	}
	public function __destruct () {
		fclose($this->stream);
	}
}
