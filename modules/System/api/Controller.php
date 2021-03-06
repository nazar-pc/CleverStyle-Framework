<?php
/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license    0BSD
 */
namespace cs\modules\System\api;
use
	cs\modules\System\api\Controller\admin,
	cs\modules\System\api\Controller\general,
	cs\modules\System\api\Controller\profile;

class Controller {
	use
		admin\core_options_common,
		admin\about_server,
		admin\blocks,
		admin\databases,
		admin\groups,
		admin\languages,
		admin\mail,
		admin\modules,
		admin\optimization,
		admin\permissions,
		admin\security,
		admin\site_info,
		admin\storages,
		admin\system,
		admin\themes,
		admin\upload,
		admin\users,
		admin\users\general,
		admin\users\groups,
		admin\users\permissions,
		general,
		profile;
	public static function blank () { }
}
