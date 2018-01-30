/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license    0BSD
 */
Polymer(
	is			: 'cs-system-admin-system'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_admin_system_')
		cs.Polymer.behaviors.admin.System.settings
	]
	properties	:
		settings_api_url	: 'api/System/admin/system'
)
