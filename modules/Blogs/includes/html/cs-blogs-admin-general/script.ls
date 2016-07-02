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
		cs.api('get_settings ' + @settings_api_url).then (settings) !~>
			@set('settings', settings)
	_save : !->
		cs.api('save_settings ' + @settings_api_url, @settings).then !~>
			@_reload_settings()
			cs.ui.notify(@L.changes_saved, 'success', 5)
)
