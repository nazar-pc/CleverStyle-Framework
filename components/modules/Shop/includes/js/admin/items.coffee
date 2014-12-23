###*
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
$ ->
	L = cs.Language
	set_attribute_types			= [1, 2, 6, 9]	# Attributes types that represents sets: TYPE_INT_SET, TYPE_FLOAT_SET, TYPE_STRING_SET, TYPE_COLOR_SET
	color_set_attribute_type	= [1, 2, 6, 9]	# Attributes types that represents color set: TYPE_COLOR_SET
	string_attribute_types		= [5]			# Attributes types that represents string: TYPE_STRING
	make_modal = (attributes, categories, title, action) ->
		attributes		= do ->
			attributes_ = {}
			for attribute, attribute of attributes
				attributes_[attribute.id] = attribute
			attributes_
		categories		= do ->
			categories_ = {}
			for category, category of categories
				categories_[category.id] = category
			categories_
		categories_list	= do ->
			categories_list_	= {
				'-' : """<option disabled>#{L.none}</option>"""
			}
			keys				= ['-']
			for category, category of categories
				parent_category = parseInt(category.parent)
				while parent_category && parent_category != category
					parent_category = categories[parent_category]
					if parent_category.parent == category.id
						break
					category.title	= parent_category.title + ' :: ' + category.title
					parent_category	= parseInt(parent_category.parent)
				categories_list_[category.title]	= """<option value="#{category.id}">#{category.title}</option>"""
				keys.push(category.title)
			keys.sort()
			for key in keys
				categories_list_[key]
		categories_list	= categories_list.join('')
		modal			= $.cs.simple_modal("""<form>
			<h3 class="cs-center">#{title}</h3>
			<p>
				#{L.shop_category}: <select name="category" required>#{categories_list}</select>
			</p>
			<div></div>
		</form>""")
		modal.item_data			= {}
		modal.update_item_data	= ->
			item	= modal.item_data
			console.log item
			modal.find('[name=price]').val(item.price)
			modal.find('[name=in_stock]').val(item.in_stock)
			modal.find("[name=soon][value=#{item.soon}]").prop('checked', true)
			modal.find("[name=listed][value=#{item.listed}]").prop('checked', true)
			if item.images
				modal.add_images(item.images)
			if item.attributes
				for attribute, value of item.attributes
					modal.find("[name='attributes[#{attribute}]']").val(value)
			if item.tags
				modal.find('[name=tags]').val(item.tags.join(', '))
		modal.find('[name=category]').change ->
			modal.find('form').serializeArray().forEach (item) ->
				value	= item.value
				name	= item.name
				switch name
					when 'tags'
						value	= value.split(',').map (v) ->
							$.trim(v)
					when 'images'
						if value
							value	= JSON.parse(value)
				if attribute = name.match(/attributes\[([0-9]+)\]/)
					if !modal.item_data.attributes
						modal.item_data.attributes	= {}
					modal.item_data.attributes[attribute[1]]	= value
				else
					modal.item_data[item.name]	= value
			$this			= $(@)
			category		= categories[$this.val()]
			attributes_list	= do ->
				for attribute in category.attributes
					attribute		= attributes[attribute]
					attribute.type	= parseInt(attribute.type)
					if set_attribute_types.indexOf(attribute.type) != -1
						values = do ->
							for value in attribute.value
								"""<option value="#{value}">#{value}</option>"""
						values = values.join('')
						color	=
							if attribute.type == color_set_attribute_type
								"""<input type="color">"""
							else
								''
						"""<p>
							#{attribute.title}:
							<select name="attributes[#{attribute.id}]">
								<option value="">#{L.none}</option>
								#{values}
							</select>
							#{color}
						</p>"""
					else if string_attribute_types.indexOf(attribute.type) != -1
						"""<p>
							#{attribute.title}: <input name="attributes[#{attribute.id}]">
						</p>"""
					else
						"""<p>
							#{attribute.title}: <textarea name="attributes[#{attribute.id}]"></textarea>
						</p>"""
			attributes_list	= attributes_list.join('')
			$this.parent().next().html("""
				<p>
					#{L.shop_price}: <input name="price" type="number" value="0" required>
				</p>
				<p>
					#{L.shop_in_stock}: <input name="in_stock" type="number" value="1" step="1">
				</p>
				<p>
					#{L.shop_available_soon}:
					<label><input type="radio" name="soon" value="1"> #{L.yes}</label>
					<label><input type="radio" name="soon" value="0" checked> #{L.no}</label>
				</p>
				<p>
					#{L.shop_listed}:
					<label><input type="radio" name="listed" value="1" checked> #{L.yes}</label>
					<label><input type="radio" name="listed" value="0"> #{L.no}</label>
				</p>
				<p>
					<div class="images"></div>
					<button type="button" class="add-images uk-button">#{L.shop_add_images}</button>
					<input type="hidden" name="images">
				</p>
				#{attributes_list}
				<p>
					#{L.shop_tags}: <input name="tags" placeholder="shop, high quality, e-commerce">
				</p>
				<p>
					<button class="uk-button" type="submit">#{action}</button>
				</p>
			""")
			images_container	= modal.find('.images')
			modal.update_images	= ->
				images	= []
				images_container.find('a').each ->
					images.push $(@).attr('href')
				modal.find('[name=images]').val(
					JSON.stringify(images)
				)
				if images.length > 1
					images_container
						.sortable(
							placeholder				: '<span>&nbsp;</span>'
							forcePlaceholderSize	: true
						)
						.on(
							'sortupdate'
							modal.update_images
						)
				else
					images_container.sortable('destroy')
			modal.add_images	= (images) ->
				images.forEach (image) ->
					images_container.append("""<span>
						<a href="#{image}" target="_blank" class="uk-thumbnail uk-thumbnail-mini">
							<img src="#{image}">
							<br>
							<button type="button" class="remove-image uk-button uk-button-danger uk-button-mini uk-width-1-1">#{L.shop_remove_image}</button>
						</a>
					</span>""")
				modal.update_images()
			if cs.file_upload
				uploader = cs.file_upload(
					modal.find('.add-images')
					(images) ->
						modal.add_images(images)
					(error) ->
						alert error.message
					null
					true
				)
				modal.on 'uk.modal.hide', ->
					uploader.destroy()
			else
				modal.find('.add-images').click ->
					image	= prompt(L.shop_image_url)
					if image
						modal.add_images([image])
			modal.on('click', '.remove-image', ->
				$(@).parent().remove()
				modal.update_images()
				return false
			)
			modal.update_item_data()
		modal
	$('html')
		.on('mousedown', '.cs-shop-item-add', ->
			$.when(
				$.getJSON('api/Shop/admin/attributes')
				$.getJSON('api/Shop/admin/categories')
			).done (attributes, categories) ->
				modal = make_modal(attributes[0], categories[0], L.shop_item_addition, L.shop_add)
				modal.find('form').submit ->
					$.ajax(
						url     : 'api/Shop/admin/items'
						type    : 'post'
						data    : $(@).serialize()
						success : ->
							alert(L.shop_added_successfully)
							location.reload()
					)
					return false
		)
		.on('mousedown', '.cs-shop-item-edit', ->
			id = $(@).data('id')
			$.when(
				$.getJSON('api/Shop/admin/attributes')
				$.getJSON('api/Shop/admin/categories')
				$.getJSON("api/Shop/admin/items/#{id}")
			).done (attributes, categories, item) ->
				modal = make_modal(attributes[0], categories[0], L.shop_item_edition, L.shop_edit)
				modal.find('form').submit ->
					$.ajax(
						url     : "api/Shop/admin/items/#{id}"
						type    : 'put'
						data    : $(@).serialize()
						success : ->
							alert(L.shop_edited_successfully)
							location.reload()
					)
					return false
				modal.item_data	= item[0]
				modal.find("[name=category]").val(item[0].category).change()
		)
		.on('mousedown', '.cs-shop-item-delete', ->
			id = $(@).data('id')
			if confirm(L.shop_sure_want_to_delete)
				$.ajax(
					url     : "api/Shop/admin/items/#{id}"
					type    : 'delete'
					success : ->
						alert(L.shop_deleted_successfully)
						location.reload()
				)
		)
