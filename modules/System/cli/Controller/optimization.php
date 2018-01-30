<?php
/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license    0BSD
 */
namespace cs\modules\System\cli\Controller;
use
	cs\modules\System\api\Controller as Api_controller;

trait optimization {
	/**
	 * @return string
	 */
	public static function optimization_help () {
		return <<<HELP
<y>System optimization</y>

<y>Methods:</y>
  <g>clean_cache</g>  Clean system cache
  <g>clean_public_cache</g> Clear the cache of scripts and styles

<y>Arguments:</y>
  <g>prefix_path</g> If present, will clean only part of cache (<g>clean_cache</g> only)

<y>Examples:</y>
  Clean the whole system cache:
    <g>./cli clean_cache:System/optimization</g>
  Clean only languages cache:
    <g>./cli clean_cache:System/optimization prefix_path=languages</g>
  Clean the whole system public cache:
    <g>./cli clean_public_cache:System/optimization</g>

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
	public static function optimization_clean_cache ($Request) {
		Api_controller::admin_optimization_clean_cache($Request);
		return "<g>OK</g>cli\n";
	}
	/**
	 * Clean public cache (CSS/JS/HTML)
	 *
	 * @return string
	 *
	 * @throws \cs\ExitException
	 */
	public static function optimization_clean_public_cache () {
		Api_controller::admin_optimization_clean_public_cache();
		return "<g>OK</g>cli\n";
	}
}
