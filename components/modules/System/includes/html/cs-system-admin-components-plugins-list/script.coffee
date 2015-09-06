###*
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
###
L = cs.Language
Polymer(
	'is'			: 'cs-system-admin-components-plugins-list'
	behaviors		: [cs.Polymer.behaviors.Language]
	ready			: ->
		$.getJSON(
			'api/System/admin/plugins'
				(plugins) =>
					plugins.forEach (plugin) ->
						plugin.class			= if plugin.active then 'cs-block-success cs-text-success' else 'cs-block-warning cs-text-warning'
						plugin.icon				= `plugin.active ? 'check' : 'minus'`
						plugin.icon_text		= `plugin.active ? L.enabled : L.disabled`
						plugin.name_localized	= L[plugin.name] || plugin.name.replace('_', ' ')
						do ->
							for prop in ['license', 'readme']
								if plugin[prop]?.type
									tag						= if plugin[prop].type == 'txt' then 'pre' else 'div'
									plugin[prop].content	= "<#{tag}>#{plugin[prop].content}</#{tag}>"
							return
						do (meta = plugin.meta) ->
							if !meta
								return
							plugin.info	= L.plugin_info(
								meta.package,
								meta.version,
								meta.description,
								meta.author,
								meta.website || L.none,
								meta.license,
								if meta.provide then [].concat(meta.provide).join(', ') else L.none,
								if meta.require then [].concat(meta.require).join(', ') else L.none,
								if meta.conflict then [].concat(meta.conflict).join(', ') else L.none,
								if meta.optional then [].concat(meta.optional).join(', ') else L.none,
								if meta.multilingual && meta.multilingual.indexOf('interface') != -1 then L.yes else L.no,
								if meta.multilingual && meta.multilingual.indexOf('content') != -1 then L.yes else L.no,
								if meta.languages then meta.languages.join(', ') else L.none
							)
							return
						return
					@set('plugins', plugins)
					return
		)
		return
);
