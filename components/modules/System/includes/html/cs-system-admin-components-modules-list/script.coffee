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
	tooltip_animation	:'{animation:true,delay:200}'
	L					: L
	modules				: []
	created				: ->
		modules = JSON.parse(@querySelector('script').innerHTML)
		modules.forEach (module) ->
			module.class			=
				switch module.active
					when -1 then 'uk-alert-danger'
					when 0 then 'uk-alert-warning'
					when 1 then 'uk-alert-success'
			module.icon				=
				switch module.active
					when -1 then 'uk-icon-times'
					when 0 then 'uk-icon-minus'
					when 1 then (if module.is_default then 'uk-icon-home' else 'uk-icon-check')
			module.icon_text		=
				switch module.active
					when -1 then L.uninstalled
					when 0 then L.disabled
					when 1 then (if module.is_default then L.default_module else L.enabled)
			module.name_localized	= L[module.name] || module.name.replace('_', ' ')
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
		@modules = modules
	domReady			: ->
		$(@shadowRoot).cs().tooltips_inside()
	generic_modal		: (event, detail, sender) ->
		$sender	= $(sender)
		index	= $sender.closest('[data-module-index]').data('module-index')
		module	= @modules[index]
		key		= $sender.data('modal-type')
		tag		= if module[key].type == 'txt' then 'pre' else 'div'
		$(
			"""<div class="uk-modal-dialog uk-modal-dialog-large">
				<div class="uk-overflow-container">
					<#{tag}>#{module[key].content}</#{tag}>
				</div>
			</div>"""
		)
			.appendTo('body')
			.cs().modal('show')
			.on 'hide.uk.modal', ->
				$(@).remove()
);
