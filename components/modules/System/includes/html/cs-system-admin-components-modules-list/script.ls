/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
L				= cs.Language
active_switch	= (uninstalled, disabled, enabled) ->
	switch @active
	| -1	=> uninstalled
	| 0		=> disabled
	| 1		=> enabled
Polymer(
	'is'		: 'cs-system-admin-components-modules-list'
	behaviors	: [cs.Polymer.behaviors.Language]
	ready : ->
		@reload()
	reload : ->
		modules <~! $.getJSON('api/System/admin/modules', _)
		modules.forEach (module) !->
			active_switch_local		= active_switch.bind(module)
			module.class			= active_switch_local(
				'cs-block-error cs-text-error'
				'cs-block-warning cs-text-warning'
				'cs-block-success cs-text-success'
			)
			module.icon				= active_switch_local(
				'times'
				'minus'
				if module.is_default then 'home' else 'check'
			)
			module.icon_text		= active_switch_local(
				L.uninstalled
				L.disabled
				if module.is_default then L.default_module else L.enabled
			)
			module.name_localized	= L[module.name] || module.name.replace('_', ' ')
			do !->
				for prop in ['api', 'license', 'readme']
					if module[prop]?.type
						tag						= if module[prop].type == 'txt' then 'pre' else 'div'
						module[prop].content	= "<#{tag}>#{module[prop].content}</#{tag}>"
			if module.meta
				module.info	= let (@ = module.meta)
					L.module_info(
						@package,
						@version,
						@description,
						@author,
						@website || L.none,
						@license,
						if @db_support then @db_support.join(', ') else L.none,
						if @storage_support then @storage_support.join(', ') else L.none,
						if @provide then [].concat(@provide).join(', ') else L.none,
						if @require then [].concat(@require).join(', ') else L.none,
						if @conflict then [].concat(@conflict).join(', ') else L.none,
						if @optional then [].concat(@optional).join(', ') else L.none,
						if @multilingual && @multilingual.indexOf('interface') != -1 then L.yes else L.no,
						if @multilingual && @multilingual.indexOf('content') != -1 then L.yes else L.no,
						if @languages then @languages.join(', ') else L.none
					)
		@set('modules', modules)
	_set_as_default : (e) !->
		cs.Event.fire(
			'admin/System/components/modules/default/before'
			name	: e.model.module.name
		).then !~>
			$.ajax(
				url		: 'api/System/admin/modules/default'
				type	: 'put'
				data	:
					module	: e.model.module.name
				success	: !~>
					@reload()
					cs.ui.notify(L.changes_saved, 'success', 5)
					cs.Event.fire(
						'admin/System/components/modules/default/after'
						name	: e.model.module.name
					)
			)
)
