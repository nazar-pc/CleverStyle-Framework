/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
Polymer(
	is			: 'cs-system-admin-site-info'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_admin_site_info_')
		cs.Polymer.behaviors.admin.System.settings
	]
	observers	: [
		'_url_changed(settings.url)'
		'_cookie_domain_changed(settings.cookie_domain)'
	]
	properties	:
		settings_api_url		: 'api/System/admin/site_info'
		timezones				: Array
		url_string				:
			observer	: '_url_string_changed'
			type		: String
		cookie_domain_string	:
			observer	: '_cookie_domain_string_changed'
			type		: String
	ready : !->
		cs.api('get api/System/timezones').then (timezones) !~>
			@timezones	=
				for description, timezone of timezones
					{timezone, description}
	_url_changed : (url) !->
		if url == undefined
			return
		url = url.join('\n')
		if @url_string !== url
			@url_string = url
	_cookie_domain_changed : (cookie_domain) !->
		if cookie_domain == undefined
			return
		cookie_domain = cookie_domain.join('\n')
		if @cookie_domain_string !== cookie_domain
			@cookie_domain_string = cookie_domain
	_url_string_changed : !->
		@set('settings.url', @url_string.split('\n'))
	_cookie_domain_string_changed : !->
		@set('settings.cookie_domain', @cookie_domain_string.split('\n'))
)
