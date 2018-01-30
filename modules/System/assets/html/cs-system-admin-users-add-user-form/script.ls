/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license    0BSD
 */
Polymer(
	is			: 'cs-system-admin-users-add-user-form'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_admin_users_')
	]
	properties	:
		email	: ''
	save : !->
		cs.api('post api/System/admin/users', {@email}).then (result) !~>
			cs.ui.alert("""
				<p class="cs-block-success cs-text-success">#{@L.user_was_added(result.login, result.password)}</p>
			""")
)
