###*
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
$ ->
	L = cs.Language
	make_modal = (attributes, categories, title, action) ->
		attributes	= do ->
			attributes_	= {}
			keys		= []
			for attribute, attribute of attributes
				attributes_[attribute.title_internal]	= """<option value="#{attribute.id}">#{attribute.title_internal}</option>"""
				keys.push(attribute.title_internal)
			keys.sort()
			for key in keys
				attributes_[key]
		attributes	= attributes.join('')
		categories	= do ->
			categories_ = {}
			for category, category of categories
				categories_[category.id] = category
			categories_
		categories	= do ->
			categories_	= {
				'-' : """<option value="0">#{L.none}</option>"""
			}
			keys		= ['-']
			for category, category of categories
				parent_category = parseInt(category.parent)
				while parent_category && parent_category != category
					parent_category = categories[parent_category]
					if parent_category.parent == category.id
						break
					category.title	= parent_category.title + ' :: ' + category.title
					parent_category	= parseInt(parent_category.parent)
				categories_[category.title]	= """<option value="#{category.id}">#{category.title}</option>"""
				keys.push(category.title)
			keys.sort()
			for key in keys
				categories_[key]
		categories	= categories.join('')
		modal		= $.cs.simple_modal("""<form>
			<h3 class="cs-center">#{title}</h3>
			<p>
				#{L.shop_parent_category}: <select name="parent" required>#{categories}</select>
			</p>
			<p>
				#{L.shop_title}: <input name="title" required>
			</p>
			<p>
				#{L.shop_description}: <textarea name="description"></textarea>
			</p>
			<p class="image uk-hidden">
				#{L.shop_image}:
				<a target="_blank" class="uk-thumbnail">
					<img>
					<br>
					<button type="button" class="remove-image uk-button uk-button-danger uk-width-1-1">#{L.shop_remove_image}</button>
				</a>
				<input type="hidden" name="image">
			</p>
			<p>
				<button type="button" class="set-image uk-button">#{L.shop_set_image}</button>
			</p>
			<p>
				#{L.shop_category_attributes}: <select name="attributes[]" multiple required>#{attributes}</select>
			</p>
			<p>
				#{L.shop_title_attribute}: <select name="title_attribute" required>#{attributes}</select>
			</p>
			<p>
				#{L.shop_visible}:
				<label><input type="radio" name="visible" value="1" checked> #{L.yes}</label>
				<label><input type="radio" name="visible" value="0"> #{L.no}</label>
			</p>
			<p>
				<button class="uk-button" type="submit">#{action}</button>
			</p>
		</form>""")
		modal.set_image	= (image) ->
			modal.find('[name=image]').val(image)
			if image
				modal.find('.image')
					.removeClass('uk-hidden')
					.find('a')
						.attr('href', image)
						.find('img')
							.attr('src', image)
			else
				modal.find('.image').addClass('uk-hidden')
		modal.find('.remove-image').click ->
			modal.set_image('')
		if cs.file_upload
			uploader = cs.file_upload(
				modal.find('.set-image')
				(image) ->
					modal.set_image(image[0])
				(error) ->
					alert error.message
			)
			modal.on 'uk.modal.hide', ->
				uploader.destroy()
		else
			modal.find('.set-image').click ->
				image	= prompt(L.shop_image_url)
				if image
					modal.set_image(image)
		modal
	$('html')
		.on('mousedown', '.cs-shop-category-add', ->
			$.when(
				$.getJSON('api/Shop/admin/attributes')
				$.getJSON('api/Shop/admin/categories')
			).done (attributes, categories) ->
				modal = make_modal(attributes[0], categories[0], L.shop_category_addition, L.shop_add)
				modal.find('form').submit ->
					$.ajax(
						url     : 'api/Shop/admin/categories'
						type    : 'post'
						data    : $(@).serialize()
						success : ->
							alert(L.shop_added_successfully)
							location.reload()
					)
					return false
		)
		.on('mousedown', '.cs-shop-category-edit', ->
			id = $(@).data('id')
			$.when(
				$.getJSON('api/Shop/admin/attributes')
				$.getJSON('api/Shop/admin/categories')
				$.getJSON("api/Shop/admin/categories/#{id}")
			).done (attributes, categories, category) ->
				modal = make_modal(attributes[0], categories[0], L.shop_category_edition, L.shop_edit)
				modal.find('form').submit ->
					$.ajax(
						url     : "api/Shop/admin/categories/#{id}"
						type    : 'put'
						data    : $(@).serialize()
						success : ->
							alert(L.shop_edited_successfully)
							location.reload()
					)
					return false
				category	= category[0]
				modal.find('[name=parent]').val(category.parent)
				modal.find('[name=title]').val(category.title)
				modal.find('[name=description]').val(category.description)
				category.attributes.forEach (attribute) ->
					modal.find("[name='attributes[]'] > [value=#{attribute}]").prop('selected', true)
				modal.find('[name=title_attribute]').val(category.title_attribute)
				modal.set_image(category.image)
				modal.find("[name=visible][value=#{category.visible}]").prop('checked', true)
		)
		.on('mousedown', '.cs-shop-category-delete', ->
			id = $(@).data('id')
			if confirm(L.shop_sure_want_to_delete)
				$.ajax(
					url     : "api/Shop/admin/categories/#{id}"
					type    : 'delete'
					success : ->
						alert(L.shop_deleted_successfully)
						location.reload()
				)
		)
