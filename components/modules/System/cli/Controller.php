<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\cli;
use
	cs\modules\System\cli\Controller\optimization;

class Controller {
	use
		optimization;
	static function __help () {
		$version = file_get_json(__DIR__.'/../meta.json')['version'];
		return <<<HELP
%gCleverStyle CMS%n version %y$version%n, CLI interface%n

%yUsage:%n
  [method:]path [arguments]

%yUniversal methods:%n
  %gcli%n  Prints all cli paths and methods available for specified path
  %ghelp%n Displays help for module or path (should be provided by developer, otherwise will fallback to %gcli%n)

%yExamples:%n
  Print all paths and methods available in the whole system:
    %g./cli cli:%n
  Print all paths and method available in System module:
    %g./cli cli:System%n
  Print all paths and method available in System module, admin sub-path:
    %g./cli cli:System/admin%n
  Print help information (this message) for System module:
    %g./cli help:System%n

HELP;
	}
}
