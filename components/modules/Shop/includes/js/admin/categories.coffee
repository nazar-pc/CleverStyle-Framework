###*
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
$ ->
	L = cs.Language
	make_modal = (attributes, categories, order_statuses, title, action) ->
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
		order_statuses	= do ->
			order_statuses_	= {}
			keys		= []
			for order_status, order_status of order_statuses
				order_statuses_[order_status.title]	= """<option value="#{order_status.id}">#{order_status.title}</option>"""
				keys.push(order_status.title)
			keys.sort()
			for key in keys
				order_statuses_[key]
		order_statuses	= order_statuses.join('')
		$.cs.simple_modal("""<form>
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
			<p>
				#{L.shop_category_attributes}: <select name="attributes[]" multiple required>#{attributes}</select>
			</p>
			<p>
				#{L.shop_title_attribute}: <select name="title_attribute" required>#{attributes}</select>
			</p>
			<p>
				#{L.shop_order_status_on_creation}: <select name="order_status_on_creation" required>#{order_statuses}</select>
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
	$('html')
		.on('mousedown', '.cs-shop-category-add', ->
			$.when(
				$.getJSON('api/Shop/admin/attributes')
				$.getJSON('api/Shop/admin/categories')
				$.getJSON('api/Shop/admin/order_statuses')
			).done (attributes, categories, order_statuses) ->
				modal = make_modal(attributes[0], categories[0], order_statuses[0], L.shop_category_addition, L.shop_add)
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
				$.getJSON('api/Shop/admin/order_statuses')
				$.getJSON("api/Shop/admin/categories/#{id}")
			).done (attributes, categories, order_statuses, category) ->
				modal = make_modal(attributes[0], categories[0], order_statuses[0], L.shop_category_edition, L.shop_edit)
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
				modal.find('[name=order_status_on_creation]').val(category.order_status_on_creation)
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
