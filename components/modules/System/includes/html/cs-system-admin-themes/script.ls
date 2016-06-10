/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
Polymer(
	'is'		: 'cs-system-admin-themes'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_admin_appearance_')
		cs.Polymer.behaviors.admin.System.components
		cs.Polymer.behaviors.admin.System.upload
	]
	properties	:
		current_theme	: String
	ready : !->
		@reload()
	reload : !->
		cs.api([
			'get api/System/admin/themes'
			'get api/System/admin/themes/current'
		]).then ([themes, current_theme]) !~>
			@current_theme	= current_theme
			themes.forEach (theme) !~>
				current				= theme.name == @current_theme
				theme.class			= if !current then 'cs-block-warning cs-text-warning' else 'cs-block-success cs-text-success'
				theme.icon			= if !current then 'minus' else 'check'
				theme.can_delete	= !current && theme.name != 'CleverStyle'
				do !->
					for prop in ['license', 'readme']
						if theme[prop]?.type
							tag					= if theme[prop].type == 'txt' then 'pre' else 'div'
							theme[prop].content	= "<#tag>#{theme[prop].content}</#tag>"
			@set('themes', themes)
	/**
	 * Provides next events:
	 *  admin/System/components/themes/current/before
	 *  {name : theme_name}
	 *
	 *  admin/System/components/themes/current/after
	 *  {name : theme_name}
	 */
	_set_current : (e) !->
		@current_theme = e.model.theme.name
		cs.Event.fire(
			'admin/System/components/themes/current/before'
			name	: @current_theme
		).then !~>
			cs.api('put api/System/admin/themes/current', {theme : @current_theme}).then !~>
				cs.ui.notify(@L.changes_saved, 'success', 5)
				@reload()
				cs.Event.fire(
					'admin/System/components/themes/current/after'
					name	: @current_theme
				)
	_remove_completely : (e) !->
		@_remove_completely_component(e.model.theme.name, 'themes')
	/**
	 * Provides next events:
	 *  admin/System/components/themes/update/before
	 *  {name : theme_name}
	 *
	 *  admin/System/components/themes/update/after
	 *  {name : theme_name}
	 */
	_upload : !->
		@_upload_package(@$.file).then (meta) !~>
			if meta.category != 'themes' || !meta.package || !meta.version
				cs.ui.notify(@L.this_is_not_theme_installer_file, 'error', 5)
				return
			# Looking for already present theme
			for theme in @themes
				if theme.name == meta.package
					if meta.version == theme.meta.version
						cs.ui.notify(@L.update_theme_impossible_same_version(meta.package, meta.version), 'warning', 5)
						return
					@_update_component(theme.meta, meta)
					return
			# If theme is not present yet - lets just extract it
			@_extract(meta)
	_extract : (meta) !->
		cs.api('extract api/System/admin/themes').then !~>
			@reload()
			cs.ui.notify(@L.changes_saved, 'success', 5)
)
