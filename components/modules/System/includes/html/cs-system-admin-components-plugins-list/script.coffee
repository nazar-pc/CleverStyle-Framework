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
	plugins				: []
	created				: ->
		plugins = JSON.parse(@querySelector('script').innerHTML)
		plugins.forEach (plugin) ->
			plugin.class			= if plugin.active then 'uk-alert-success' else 'uk-alert-warning'
			plugin.icon				= if plugin.active then 'uk-icon-check' else 'uk-icon-minus'
			plugin.icon_text		= if plugin.active then L.enabled else L.disabled
			plugin.name_localized	= L[plugin.name] || plugin.name.replace('_', ' ')
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
		@plugins = plugins
	domReady			: ->
		$(@shadowRoot).cs().tooltips_inside()
	generic_modal		: (event, detail, sender) ->
		$sender	= $(sender)
		index	= $sender.closest('[data-plugin-index]').data('plugin-index')
		plugin	= @plugins[index]
		key		= $sender.data('modal-type')
		tag		= if plugin[key].type == 'txt' then 'pre' else 'div'
		$(
			"""<div class="uk-modal-dialog uk-modal-dialog-large">
				<div class="uk-overflow-container">
					<#{tag}>#{plugin[key].content}</#{tag}>
				</div>
			</div>"""
		)
			.appendTo('body')
			.cs().modal('show')
			.on 'hide.uk.modal', ->
				$(@).remove()
);
