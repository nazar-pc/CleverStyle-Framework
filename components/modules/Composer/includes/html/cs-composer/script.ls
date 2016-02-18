/**
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
L	= cs.Language('composer_')
Polymer(
	'is'		: 'cs-composer'
	behaviors	: [
		cs.Polymer.behaviors.Language('composer_')
	]
	properties	:
		action		: String
		canceled	: Boolean
		force		: Boolean
		package		: String
		status		: String
		category	: String
	ready : !->
		cs.Event.once('admin/Composer/canceled', !~>
			@canceled	= true
		)
		$.ajax(
			url		: 'api/Composer'
			type	: if @action == 'uninstall' then 'delete' else 'post'
			data	:
				name		: @package
				category	: @category
				force		: @force
			success	: (result) !~>
				status =
					switch result.code
					| 0 => L.updated_successfully
					| 1 => L.update_failed
					| 2 => L.dependencies_conflict
				@status	= status
				if result.description
					$(@$.result)
						.show()
						.html(result.description)
				if !result.code && !@force
					setTimeout (!->
						cs.Event.fire('admin/Composer/updated')
					), 2000
		)
		setTimeout(@~_update_progress, 1000)
	_update_progress : !->
		$.getJSON(
			'api/Composer'
			(data) !~>
				if @status || @canceled
					return
				if @parentElement.$?.content
					scroll_after = @parentElement.$.content.scrollHeight - @parentElement.$.content.offsetHeight == @parentElement.$.content.scrollTop
				$(@$.result)
					.show()
					.html(data)
				if scroll_after
					@parentElement.$.content.scrollTop = @parentElement.$.content.scrollHeight - @parentElement.$.content.offsetHeight
				setTimeout(@~_update_progress, 1000)
		)
)
