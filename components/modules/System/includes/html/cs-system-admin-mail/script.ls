/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
L = cs.Language
Polymer(
	'is'		: 'cs-system-admin-mail'
	behaviors	: [
		cs.Polymer.behaviors.Language
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
		email = prompt(L.email)
		if email
			$.ajax(
				url		: 'api/System/admin/mail'
				data	:
					email	: email
				type	: 'send_test_email'
				success	: ->
					alert(L.done)
				error	: ->
					alert(L.test_email_sending_failed)
			)
)
