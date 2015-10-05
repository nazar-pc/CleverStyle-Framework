/**
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
L	= cs.Language
Polymer(
	'is'		: 'cs-composer'
	behaviors	: [cs.Polymer.behaviors.Language]
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
			type	: if @action != 'install' then 'delete' else 'post'
			data	:
				name		: @package
				category	: @category
				force		: @force
			success	: (result) !~>
				status =
					switch result.code
					| 0 => L.composer_updated_successfully
					| 1 => L.composer_update_failed
					| 2 => L.composer_dependencies_conflict
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
				$(@$.result)
					.show()
					.html(data)
				setTimeout(@~_update_progress, 1000)
		)
)
