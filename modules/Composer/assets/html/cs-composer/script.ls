/**
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	is			: 'cs-composer'
	behaviors	: [
		cs.Polymer.behaviors.Language('composer_')
	]
	properties	:
		action		: String
		force		: Boolean
		package		: String
		status		: String
	ready : !->
		cs.Event.once('admin/Composer/canceled', !~>
			@_stop_updates	= true
		)
		method	= if @action == 'uninstall' then 'delete' else 'post'
		data	=
			name		: @package
			force		: @force
		Promise.all([
			cs.api("#method api/Composer", data)
			cs.Language.ready()
		])
			.then ([result]) !~>
				@_save_scroll_position()
				@status =
					switch result.code
					| 0 => @L.updated_successfully
					| 1 => @L.update_failed
					| 2 => @L.dependencies_conflict
				if result.description
					@$.result.innerHTML	= result.description
					@_restore_scroll_position()
				if !result.code
					setTimeout (!->
						cs.Event.fire('admin/Composer/updated')
					), 2000
			.catch !~>
				@_stop_updates	= true
				@status			= @L.update_failed
		setTimeout(@~_update_progress, 1000)
	_update_progress : !->
		cs.api('get api/Composer').then (data) !~>
			if @_stop_updates
				return
			@_save_scroll_position()
			@$.result.innerHTML	= data
			@_restore_scroll_position()
			setTimeout(@~_update_progress, 1000)
	_save_scroll_position : !->
		@_scroll_after	= false
		if @parentElement.$?.content
			@_scroll_after = @parentElement.$.content.scrollHeight - @parentElement.$.content.offsetHeight == @parentElement.$.content.scrollTop
	_restore_scroll_position : !->
		if @_scroll_after
			@parentElement.$.content.scrollTop = @parentElement.$.content.scrollHeight - @parentElement.$.content.offsetHeight
)
