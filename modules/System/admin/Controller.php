<?php
/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license    0BSD
 */
namespace cs\modules\System\admin;
use
	cs\Page;

class Controller {
	/**
	 * @param \cs\Request $Request
	 */
	public static function index ($Request) {
		$Page = Page::instance();
		if ($Request->route_path(2) == 'phpinfo') {
			$Page->interface = false;
			$Page->Content   = ob_wrapper(
				function () {
					phpinfo();
				}
			);
			return;
		}
		$Page->title('%1$s');
		$Page->title('%2$s');
	}
}
