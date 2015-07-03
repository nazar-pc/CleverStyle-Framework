<?php
/**
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Composer;
use
	Composer\Console\Application as ComposerApplication;

class Application extends ComposerApplication {
	/**
	 * @var callable
	 */
	private $composer_callback;
	/**
	 * @param callable $composer_callback
	 */
	function __construct ($composer_callback) {
		$this->composer_callback = $composer_callback;
		parent::__construct();
	}
	/**
	 * @param bool|true  $required
	 * @param bool|false $disablePlugins
	 *
	 * @return \Composer\Composer
	 *
	 * @throws \Composer\Json\JsonValidationException
	 */
	function getComposer ($required = true, $disablePlugins = false) {
		$Composer = parent::getComposer($required, $disablePlugins);
		if ($this->composer_callback) {
			call_user_func($this->composer_callback, $Composer);
		}
		return $Composer;
	}
}
