/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license    0BSD
 */
Polymer(
	is			: 'cs-system-admin-security'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_admin_security_')
		cs.Polymer.behaviors.admin.System.settings
	]
	properties	:
		settings_api_url	: 'api/System/admin/security'
)
