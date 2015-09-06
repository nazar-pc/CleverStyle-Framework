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
	'is'			: 'cs-system-admin-components-modules-list'
	behaviors		: [cs.Polymer.behaviors.Language]
	ready			: ->
		$.getJSON(
			'api/System/admin/modules'
			(modules) =>
				modules.forEach (module) ->
					module.class			=
						switch module.active
							when -1 then 'cs-block-error cs-text-error'
							when 0 then 'cs-block-warning cs-text-warning'
							when 1 then 'cs-block-success cs-text-success'
					module.icon				=
						switch module.active
							when -1 then 'times'
							when 0 then 'minus'
							when 1 then (if module.is_default then 'home' else 'check')
					module.icon_text		=
						switch module.active
							when -1 then L.uninstalled
							when 0 then L.disabled
							when 1 then (if module.is_default then L.default_module else L.enabled)
					module.name_localized	= L[module.name] || module.name.replace('_', ' ')
					do ->
						for prop in ['api', 'license', 'readme']
							if module[prop]?.type
								tag						= if module[prop].type == 'txt' then 'pre' else 'div'
								module[prop].content	= "<#{tag}>#{module[prop].content}</#{tag}>"
						return
					do (meta = module.meta) ->
						if !meta
							return
						module.info	= L.module_info(
							meta.package,
							meta.version,
							meta.description,
							meta.author,
							meta.website || L.none,
							meta.license,
							if meta.db_support then meta.db_support.join(', ') else L.none,
							if meta.storage_support then meta.storage_support.join(', ') else L.none,
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
				@set('modules', modules)
				return
		)
		return
)
