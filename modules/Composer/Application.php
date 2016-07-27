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
	Composer\Console\Application as Composer_application;

class Application extends Composer_application {
	/**
	 * @var callable
	 */
	private $composer_callback;
	/**
	 * @param callable $composer_callback
	 */
	public function __construct ($composer_callback) {
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
	public function getComposer ($required = true, $disablePlugins = false) {
		$Composer = parent::getComposer($required, $disablePlugins);
		if ($callback = $this->composer_callback) {
			$callback($Composer);
		}
		return $Composer;
	}
}
