###*
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
$ ->
	L = cs.Language
	set_attribute_types = [1, 2, 6, 9] # Attributes types that represents sets: TYPE_INT_SET, TYPE_FLOAT_SET, TYPE_STRING_SET, TYPE_COLOR_SET
	$('html')
		.on('click', '.cs-shop-attribute-add', ->
			$.ajax(
				url     : 'api/Shop/admin/attributes/types'
				type    : 'get'
				success : (types) ->
					types =
						for index, type of types
							"""<option value="#{index}">#{type}</option>"""
					types = types.join('')
					modal = $.cs.simple_modal("""
						<h3 class="cs-center">#{L.shop_attribute_addition}</h3>
						<p>
							#{L.shop_attribute_type}: <select name="type">#{types}</select>
						</p>
						<p>
							#{L.shop_possible_values}: <textarea name="value"></textarea>
						</p>
						<p>
							#{L.shop_title}: <input name="title">
						</p>
						<p>
							#{L.shop_internal_title}: <input name="internal_title">
						</p>
						<p>
							<button class="uk-button">#{L.shop_add}</button>
						</p>
					""")
					modal
						.on('click', 'button', ->
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
									internal_title : modal.find('[name=internal_title]').val()
									value          : value
								success : ->
									alert(L.shop_added_successfully)
									location.reload()
							)
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
		.on('click', '.cs-shop-attribute-edit', ->
			id = $(@).data('id')
			$.ajax(
				url     : 'api/Shop/admin/attributes/types'
				type    : 'get'
				success : (types) ->
					$.ajax(
						url     : "api/Shop/admin/attributes/#{id}"
						type    : 'get'
						success : (attribute) ->
							types =
								for index, type of types
									"""<option value="#{index}">#{type}</option>"""
							types = types.join('')
							modal = $.cs.simple_modal("""
								<h3 class="cs-center">#{L.shop_attribute_addition}</h3>
								<p>
									#{L.shop_attribute_type}: <select name="type">#{types}</select>
								</p>
								<p>
									#{L.shop_possible_values}: <textarea name="value"></textarea>
								</p>
								<p>
									#{L.shop_title}: <input name="title">
								</p>
								<p>
									#{L.shop_internal_title}: <input name="internal_title">
								</p>
								<p>
									<button class="uk-button">#{L.shop_edit}</button>
								</p>
							""")
							modal
								.on('click', 'button', ->
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
											internal_title : modal.find('[name=internal_title]').val()
											value          : value
										success : ->
											alert(L.shop_edited_successfully)
											location.reload()
									)
								)
								.on('change', '[name=type]', ->
									value_container = $(@).parent().next()
									type = $(@).val()
									if set_attribute_types.indexOf(parseInt(type)) != -1
										value_container.show()
									else
										value_container.hide()
							)
							modal.find('[name=type]').val(attribute.type).change()
							modal.find('[name=value]').val(if attribute.value then attribute.value.join('\n') else '')
							modal.find('[name=title]').val(attribute.title)
							modal.find('[name=internal_title]').val(attribute.internal_title)
					)
			)
		)
		.on('click', '.cs-shop-attribute-delete', ->
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
