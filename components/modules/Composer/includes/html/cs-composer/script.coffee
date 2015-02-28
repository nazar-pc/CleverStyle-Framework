###*
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
L = cs.Language
Polymer(
	composer_updating_text	: L.composer_composer_updating
	ready					: ->
		$.ajax(
			url		: 'api/Composer'
			type	: if cs.composer.add then  'post' else 'delete'
			data	:
				name	: cs.composer.name
				type	: cs.composer.type
			success	: (result) =>
				if result.description
					$(@$.result)
						.show()
						.html(result.description)
				@status =
					switch result.code
						when 0 then L.composer_updated_successfully
						when 1 then L.composer_update_failed
						when 2 then L.composer_dependencies_conflict
				if !result.code
					setTimeout (->
						cs.composer.modal.trigger('hide')
					), 2000
				cs.composer.button.off('click.cs-composer').click()
		)
)
