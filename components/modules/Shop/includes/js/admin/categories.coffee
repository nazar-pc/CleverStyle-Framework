###*
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2015, Nazar Mokrynskyi
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
			categories_	= {}
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
		modal		= $(cs.ui.simple_modal("""<form>
			<h3 class="cs-text-center">#{title}</h3>
			<p>
				#{L.shop_parent_category}:
				<select is="cs-select" name="parent" required>
					<option value="0">#{L.none}</option>
					#{categories}
				</select>
			</p>
			<p>
				#{L.shop_title}: <input is="cs-input-text" name="title" required>
			</p>
			<p>
				#{L.shop_description}: <textarea is="cs-textarea" autosize name="description"></textarea>
			</p>
			<p class="image" hidden>
				#{L.shop_image}:
				<a target="_blank" class="uk-thumbnail">
					<img>
					<br>
					<button is="cs-button" force-compact type="button" class="remove-image uk-width-1-1">#{L.shop_remove_image}</button>
				</a>
				<input type="hidden" name="image">
			</p>
			<p>
				<button is="cs-button" tight type="button" class="set-image">#{L.shop_set_image}</button>
				<progress is="cs-progress" hidden></progress>
			</p>
			<p>
				#{L.shop_category_attributes}: <select is="cs-select" name="attributes[]" multiple required size="5">#{attributes}</select>
			</p>
			<p>
				#{L.shop_title_attribute}: <select is="cs-select" name="title_attribute" required>#{attributes}</select>
			</p>
			<p>
				#{L.shop_description_attribute}:
				<select is="cs-select" name="description_attribute" required>
					<option value="0">#{L.none}</option>
					#{attributes}
				</select>
			</p>
			<p>
				#{L.shop_visible}:
				<label is="cs-label-button"><input type="radio" name="visible" value="1" checked> #{L.yes}</label>
				<label is="cs-label-button"><input type="radio" name="visible" value="0"> #{L.no}</label>
			</p>
			<p>
				<button is="cs-button" primary type="submit">#{action}</button>
			</p>
		</form>"""))
		modal.set_image	= (image) ->
			modal.find('[name=image]').val(image)
			if image
				modal.find('.image')
					.removeAttr('hidden')
					.find('a')
						.attr('href', image)
						.find('img')
							.attr('src', image)
			else
				modal.find('.image').attr('hidden')
		modal.find('.remove-image').click ->
			modal.set_image('')
		if cs.file_upload
			do ->
				progress	= modal.find('.set-image').next()[0]
				uploader	= cs.file_upload(
					modal.find('.set-image')
					(image) ->
						progress.hidden = true
						modal.set_image(image[0])
					(error) ->
						progress.hidden = true
						alert error.message
					(percents) ->
						progress.value	= percents
						progress.hidden	= false
				)
				modal.on 'hide.uk.modal', ->
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
				modal.find('[name=description_attribute]').val(category.description_attribute)
				modal.set_image(category.image)
				modal.find("[name=visible][value=#{category.visible}]").prop('checked', true)
		)
		.on('mousedown', '.cs-shop-category-delete', ->
			id = $(@).data('id')
			if confirm(L.shop_sure_want_to_delete_category)
				$.ajax(
					url     : "api/Shop/admin/categories/#{id}"
					type    : 'delete'
					success : ->
						alert(L.shop_deleted_successfully)
						location.reload()
				)
		)
