/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license    0BSD
 */
Polymer(
	is			: 'cs-system-admin-about-server'
	behaviors	: [
		cs.Polymer.behaviors.computed_bindings
		cs.Polymer.behaviors.Language('system_admin_about_server_')
	]
	properties	:
		server_config	: Object
	ready : !->
		cs.api('get api/System/admin/about_server').then (@server_config) !~>
)
