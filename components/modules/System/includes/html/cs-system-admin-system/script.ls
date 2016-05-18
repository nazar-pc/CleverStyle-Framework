/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
Polymer(
	'is'		: 'cs-system-admin-system'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_admin_system_')
		cs.Polymer.behaviors.admin.System.settings
	]
	properties	:
		settings_api_url	: 'api/System/admin/system'
)
