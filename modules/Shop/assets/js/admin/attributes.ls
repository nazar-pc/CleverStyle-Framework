/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
$ <-! require(['jquery'], _)
<-! $
set_attribute_types	= [1, 2, 6, 9] # Attributes types that represents sets: TYPE_INT_SET, TYPE_FLOAT_SET, TYPE_STRING_SET, TYPE_COLOR_SET
make_modal			= (types, L, title, action) ->
	types		= for index, type of types
		"""<option value="#index">#type</option>"""
	types		= types.join('')
	cs.ui.simple_modal("""<cs-form><form>
		<h3 class="cs-text-center">#title</h3>
		<label>#{L.attribute_type}</label>
		<cs-select><select name="type" required>#types</select></cs-select>
		<label>#{L.possible_values}</label>
		<textarea is="cs-textarea" autosize name="value"></textarea>
		<label>#{L.title}</label>
		<cs-input-text><input name="title" required></cs-input-text>
		<label>#{L.title_internal}</label>
		<cs-input-text><input name="title_internal" required></cs-input-text>
		<br>
		<cs-button primary><button type="submit">#action</button></cs-button>
	</form></cs-form>""")
$('html')
	.on('mousedown', '.cs-shop-attribute-add', !->
		Promise.all([
			cs.api('get api/Shop/admin/attributes/types')
			cs.Language('shop_').ready()
		]).then ([types, L]) !->
			$modal	= $(make_modal(types, L, L.attribute_addition, L.add))
			$modal
				.on('submit', 'form', !->
					type	= $modal.find('[name=type]').val()
					value	=
						if set_attribute_types.indexOf(parseInt(type)) != -1
							$modal.find('[name=value]').val().split('\n')
						else
							''
					data	=
						type           : type
						title          : $modal.find('[name=title]').val()
						title_internal : $modal.find('[name=title_internal]').val()
						value          : value
					cs.api('post api/Shop/admin/attributes', data)
						.then -> cs.ui.alert(L.added_successfully)
						.then(location~reload)
					return false
				)
				.on('change', '[name=type]', !->
					value_container = $(@).parent().next()
					type = $(@).val()
					if set_attribute_types.indexOf(parseInt(type)) != -1
						value_container.show()
					else
						value_container.hide()
				)
	)
	.on('mousedown', '.cs-shop-attribute-edit', !->
		id = $(@).data('id')
		Promise.all([
			cs.api([
				'get api/Shop/admin/attributes/types'
				"get api/Shop/admin/attributes/#id"
			])
			cs.Language('shop_').ready()
		]).then ([[types, attribute], L]) !->
			$modal	= $(make_modal(types, L, L.attribute_edition, L.edit))
			$modal
				.on('submit', 'form', !->
					type	= $modal.find('[name=type]').val()
					value	=
						if set_attribute_types.indexOf(parseInt(type)) != -1
							$modal.find('[name=value]').val().split('\n')
						else
							''
					data	=
						type           : type
						title          : $modal.find('[name=title]').val()
						title_internal : $modal.find('[name=title_internal]').val()
						value          : value
					cs.api("put api/Shop/admin/attributes/#id", data)
						.then -> cs.ui.alert(L.edited_successfully)
						.then(location~reload)
					return false
				)
				.on('change', '[name=type]', !->
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
	.on('mousedown', '.cs-shop-attribute-delete', !->
		id = $(@).data('id')
		cs.Language('shop_').ready().then (L) !->
			cs.ui.confirm(L.sure_want_to_delete)
				.then -> cs.api("delete api/Shop/admin/attributes/#id")
				.then -> cs.ui.alert(L.deleted_successfully)
				.then(location~reload)
	)
