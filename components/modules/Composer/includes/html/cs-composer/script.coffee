###*
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
L			= cs.Language
MODE_DELETE	= 2
Polymer(
	'is'		: 'cs-composer'
	behaviors	: [cs.Polymer.behaviors.Language]
	ready		: ->
		$.ajax(
			url		: 'api/Composer'
			type	: if cs.composer.mode == MODE_DELETE then 'delete' else 'post'
			data	:
				name	: cs.composer.name
				type	: cs.composer.type
				force	: cs.composer.force
			success	: (result) =>
				status =
					switch result.code
						when 0 then L.composer_updated_successfully
						when 1 then L.composer_update_failed
						when 2 then L.composer_dependencies_conflict
				@set('status', status)
				if result.description
					$(@$.result)
						.show()
						.html(result.description)
				if !result.code && !cs.composer.force
					setTimeout (->
						cs.composer.modal.trigger('hide')
					), 2000
				cs.composer.button.off('click.cs-composer').click()
		)
		setTimeout (=>
			@update_progress()
		), 1000
	update_progress	: ->
		$.getJSON(
			'api/Composer'
			(data) =>
				# @offsetHeight will be 0 if someone will close modal, no need to update data anymore
				if @status || !@offsetHeight
					return
				$(@$.result)
					.show()
					.html(data)
				setTimeout (=>
					@update_progress()
				), 1000
		)
)
