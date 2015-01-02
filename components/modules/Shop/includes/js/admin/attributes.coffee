###*
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
$ ->
	L					= cs.Language
	set_attribute_types	= [1, 2, 6, 9] # Attributes types that represents sets: TYPE_INT_SET, TYPE_FLOAT_SET, TYPE_STRING_SET, TYPE_COLOR_SET
	make_modal			= (types, title, action) ->
		types		=
			for index, type of types
				"""<option value="#{index}">#{type}</option>"""
		types		= types.join('')
		$.cs.simple_modal("""<form>
			<h3 class="cs-center">#{title}</h3>
			<p>
				#{L.shop_attribute_type}: <select name="type" required>#{types}</select>
			</p>
			<p>
				#{L.shop_possible_values}: <textarea name="value"></textarea>
			</p>
			<p>
				#{L.shop_title}: <input name="title" required>
			</p>
			<p>
				#{L.shop_title_internal}: <input name="title_internal" required>
			</p>
			<p>
				<button class="uk-button" type="submit">#{action}</button>
			</p>
		</form>""")
	$('html')
		.on('mousedown', '.cs-shop-attribute-add', ->
			$.getJSON('api/Shop/admin/attributes/types', (types) ->
				modal	= make_modal(types, L.shop_attribute_addition, L.shop_add)
				modal
					.on('submit', 'form', ->
						type = modal.find('[name=type]').val()
						value =
							if set_attribute_types.indexOf(parseInt(type)) != -1
								modal.find('[name=value]').val().split('\n')
							else
								''
						$.ajax(
							url     : 'api/Shop/admin/attributes'
							type    : 'post'
							data    :
								type           : type
								title          : modal.find('[name=title]').val()
								title_internal : modal.find('[name=title_internal]').val()
								value          : value
							success : ->
								alert(L.shop_added_successfully)
								location.reload()
						)
						return false
					)
					.on('change', '[name=type]', ->
						value_container = $(@).parent().next()
						type = $(@).val()
						if set_attribute_types.indexOf(parseInt(type)) != -1
							value_container.show()
						else
							value_container.hide()
				)
			)
		)
		.on('mousedown', '.cs-shop-attribute-edit', ->
			id = $(@).data('id')
			$.when(
				$.getJSON('api/Shop/admin/attributes/types')
				$.getJSON("api/Shop/admin/attributes/#{id}")
			).done (types, attribute) ->
				modal	= make_modal(types[0], L.shop_attribute_edition, L.shop_edit)
				modal
					.on('submit', 'form', ->
						type = modal.find('[name=type]').val()
						value =
							if set_attribute_types.indexOf(parseInt(type)) != -1
								modal.find('[name=value]').val().split('\n')
							else
								''
						$.ajax(
							url     : "api/Shop/admin/attributes/#{id}"
							type    : 'put'
							data    :
								type           : type
								title          : modal.find('[name=title]').val()
								title_internal : modal.find('[name=title_internal]').val()
								value          : value
							success : ->
								alert(L.shop_edited_successfully)
								location.reload()
						)
						return false
					)
					.on('change', '[name=type]', ->
						value_container = $(@).parent().next()
						type = $(@).val()
						if set_attribute_types.indexOf(parseInt(type)) != -1
							value_container.show()
						else
							value_container.hide()
				)
				attribute	= attribute[0]
				modal.find('[name=type]').val(attribute.type).change()
				modal.find('[name=value]').val(if attribute.value then attribute.value.join('\n') else '')
				modal.find('[name=title]').val(attribute.title)
				modal.find('[name=title_internal]').val(attribute.title_internal)
		)
		.on('mousedown', '.cs-shop-attribute-delete', ->
			id = $(@).data('id')
			if confirm(L.shop_sure_want_to_delete)
				$.ajax(
					url     : "api/Shop/admin/attributes/#{id}"
					type    : 'delete'
					success : ->
						alert(L.shop_deleted_successfully)
						location.reload()
				)
		)
