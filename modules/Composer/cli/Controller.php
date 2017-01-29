<?php
/**
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Composer\cli;
use
	cs\modules\Composer\Composer,
	cs\Event,
	cs\ExitException;

class Controller {
	public static function index_help () {
		return <<<HELP
<y>Composer module</y>

<y>Methods:</y>
  <g>update</g>  Update Composer using system configuration
  <g>clean</g>   Clean Composer's files (composer.json/lock as well as vendor directory, useful if <g>update</g> fails)

<y>Examples:</y>
  Update Composer:
    <g>./cli update:Composer</g>
  Clean Composer's files:
    <g>./cli clean:Composer</g>

HELP;
	}
	/**
	 * @param \cs\Request  $Request
	 * @param \cs\Response $Response
	 */
	public static function index_update (
		/** @noinspection PhpUnusedParameterInspection */
		$Request,
		$Response
	) {
		$callback = function ($data) {
			echo $data['message'];
		};
		$Event    = Event::instance();
		$Event->on('Composer/update_progress', $callback);
		$Response->code = Composer::instance()->force_update()['code'];
		$Event->off('Composer/update_progress', $callback);
	}
	/**
	 * @return string
	 *
	 * @throws ExitException
	 */
	public static function index_clean () {
		if (!rmdir_recursive(STORAGE.'/Composer')) {
			throw new ExitException(500);
		}
		return '<g>OK</g>';
	}
}
