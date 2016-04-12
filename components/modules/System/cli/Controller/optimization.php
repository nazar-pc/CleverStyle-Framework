<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\cli\Controller;
use
	cs\modules\System\api\Controller as Api_controller;

trait optimization {
	/**
	 * @return string
	 */
	static function optimization_help () {
		return <<<HELP
%ySystem optimization%n

%yMethods:%n
  %gclean_cache%n  Clean system cache
  %gclean_pcache%n Clear the cache of scripts and styles

%yArguments:%n
  %gprefix_path%n If present, will clean only part of cache (%gclean_cache%n only)

%yExamples:%n
  Clean the whole system cache:
    %g./cli clean_cache:System/optimization%n
  Clean only languages cache:
    %g./cli clean_cache:System/optimization prefix_path=languages%n
  Clean the whole system public cache:
    %g./cli clean_pcache:System/optimization%n

HELP;
	}
	/**
	 * Clean cache
	 *
	 * @param \cs\Request $Request
	 *
	 * @return string
	 *
	 * @throws \cs\ExitException
	 */
	static function optimization_clean_cache ($Request) {
		Api_controller::admin_optimization_clean_cache($Request);
		return "%gOK%n\n";
	}
	/**
	 * Clean public cache (CSS/JS/HTML)
	 *
	 * @return string
	 *
	 * @throws \cs\ExitException
	 */
	static function optimization_clean_pcache () {
		Api_controller::admin_optimization_clean_pcache();
		return "%gOK%n\n";
	}
}
