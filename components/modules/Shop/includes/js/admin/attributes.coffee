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
		cs.ui.simple_modal("""<form is="cs-form">
			<h3 class="cs-text-center">#{title}</h3>
			<label>#{L.shop_attribute_type}</label>
			<select is="cs-select" name="type" required>#{types}</select>
			<label>#{L.shop_possible_values}</label>
			<textarea is="cs-textarea" autosize name="value"></textarea>
			<label>#{L.shop_title}</label>
			<input is="cs-input-text" name="title" required>
			<label>#{L.shop_title_internal}</label>
			<input is="cs-input-text" name="title_internal" required>
			<br>
			<button is="cs-button" primary type="submit">#{action}</button>
		</form>""")
	$('html')
		.on('mousedown', '.cs-shop-attribute-add', ->
			$.getJSON('api/Shop/admin/attributes/types', (types) ->
				$modal	= $(make_modal(types, L.shop_attribute_addition, L.shop_add))
				$modal
					.on('submit', 'form', ->
						type = $modal.find('[name=type]').val()
						value =
							if set_attribute_types.indexOf(parseInt(type)) != -1
								$modal.find('[name=value]').val().split('\n')
							else
								''
						$.ajax(
							url     : 'api/Shop/admin/attributes'
							type    : 'post'
							data    :
								type           : type
								title          : $modal.find('[name=title]').val()
								title_internal : $modal.find('[name=title_internal]').val()
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
			Promise.all([
				$.getJSON('api/Shop/admin/attributes/types')
				$.getJSON("api/Shop/admin/attributes/#{id}")
			]).then ([types, attribute]) ->
				$modal	= $(make_modal(types, L.shop_attribute_edition, L.shop_edit))
				$modal
					.on('submit', 'form', ->
						type = $modal.find('[name=type]').val()
						value =
							if set_attribute_types.indexOf(parseInt(type)) != -1
								$modal.find('[name=value]').val().split('\n')
							else
								''
						$.ajax(
							url     : "api/Shop/admin/attributes/#{id}"
							type    : 'put'
							data    :
								type           : type
								title          : $modal.find('[name=title]').val()
								title_internal : $modal.find('[name=title_internal]').val()
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
				$modal.find('[name=type]').val(attribute.type).change()
				$modal.find('[name=value]').val(if attribute.value then attribute.value.join('\n') else '')
				$modal.find('[name=title]').val(attribute.title)
				$modal.find('[name=title_internal]').val(attribute.title_internal)
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
