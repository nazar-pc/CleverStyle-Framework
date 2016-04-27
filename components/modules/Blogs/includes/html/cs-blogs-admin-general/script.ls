/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	'is'		: 'cs-blogs-admin-general'
	behaviors	: [
		cs.Polymer.behaviors.Language('blogs_')
	]
	properties	:
		settings			: Object
		settings_api_url	: 'api/Blogs/admin'
	ready : !->
		@_reload_settings()
	_reload_settings : !->
		$.ajax(
			url		: @settings_api_url
			type	: 'get_settings'
			success	: (settings) !~>
				@set('settings', settings)
		)
	_save : !->
		$.ajax(
			url		: @settings_api_url
			type	: 'save_settings'
			data	: @settings
			success	: !~>
				@_reload_settings()
				cs.ui.notify(@L.changes_saved, 'success', 5)
		)
)
