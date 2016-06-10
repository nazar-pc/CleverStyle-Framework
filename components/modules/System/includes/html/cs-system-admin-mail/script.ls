/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
L = cs.Language('system_admin_mail_')
Polymer(
	'is'		: 'cs-system-admin-mail'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_admin_mail_')
		cs.Polymer.behaviors.admin.System.settings
	]
	properties	:
		smtp	:
			computed	: '_smtp(settings.smtp)'
			type		: Boolean
		smtp_auth	:
			computed	: '_smtp_auth(settings.smtp, settings.smtp_auth)'
			type		: Boolean
		settings_api_url	: 'api/System/admin/mail'
	_smtp : (smtp) ->
		smtp ~= 1
	_smtp_auth : (smtp, smtp_auth) ->
		smtp ~= 1 && smtp_auth ~= 1
	_test_email : !->
		email = prompt('Email')
		if email
			cs.api('send_test_email api/System/admin/mail', {email})
				.then !->
					cs.ui.simple_modal("""<p class="cs-text-center cs-block-success cs-text-success">#{L.done}</p>""")
				.catch (o) !->
					clearTimeout(o.timeout)
					cs.ui.simple_modal("""<p class="cs-text-center cs-block-error cs-text-error">#{L.test_email_sending_failed}</p>""")
)
