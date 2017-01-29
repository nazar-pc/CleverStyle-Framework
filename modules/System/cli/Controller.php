<?php
/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2016-2017, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\cli;
use
	cs\modules\System\cli\Controller\optimization;

class Controller {
	use
		optimization;
	public static function __help () {
		$version = file_get_json(__DIR__.'/../meta.json')['version'];
		return <<<HELP
<g>CleverStyle Framework</g> version <y>$version</y>, CLI interface

<y>Usage:</y>
  [method:]path [arguments]

<y>Universal methods:</y>
  <g>cli</g>  Prints all cli paths and methods available for specified path
  <g>help</g> Displays help for module or path (should be provided by developer, otherwise will fallback to <g>cli</g>)

<y>Examples:</y>
  Print all paths and methods available in the whole system:
    <g>./cli cli:</g>
  Print all paths and method available in System module:
    <g>./cli cli:System</g>
  Print all paths and method available in System module, admin sub-path:
    <g>./cli cli:System/admin</g>
  Print help information (this message) for System module:
    <g>./cli help:System</g>

HELP;
	}
}
