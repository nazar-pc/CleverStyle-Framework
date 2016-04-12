<?php
/**
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Composer\cli;
use
	cs\modules\Composer\Composer,
	cs\Event,
	cs\ExitException;

class Controller {
	static function index_help () {
		return <<<HELP
%yComposer module%n

%yMethods:%n
  %gupdate%n  Update Composer using system configuration
  %gclean%n   Clean Composer's files (composer.json/lock as well as vendor directory, useful if %gupdate%n fails)

%yExamples:%n
  Update Composer:
    %g./cli update:Composer%n
  Clean Composer's files:
    %g./cli clean:Composer%n

HELP;
	}
	/**
	 * @param \cs\Request  $Request
	 * @param \cs\Response $Response
	 */
	static function index_update (
		/** @noinspection PhpUnusedParameterInspection */
		$Request,
		$Response
	) {
		$callback = function ($data) {
			echo $data['message'];
		};
		$Event    = Event::instance();
		$Event->on('Composer/update_progress', $callback);
		$Response->code = Composer::instance()->force_update();
		$Event->off('Composer/update_progress', $callback);
	}
	/**
	 * @return string
	 *
	 * @throws ExitException
	 */
	static function index_clean () {
		if (!rmdir_recursive(STORAGE.'/Composer')) {
			throw new ExitException(500);
		}
		return "%gOK%n\n";
	}
}
