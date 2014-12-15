###*
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
$ ->
	L = cs.Language
	make_modal = (shipping_types, order_statuses, title, action) ->
		shipping_types	= do ->
			shipping_types_ = {}
			for shipping_type, shipping_type of shipping_types
				shipping_types_[shipping_type.id] = shipping_type
			shipping_types_
		shipping_types_list	= do ->
			shipping_types_list_	= {}
			keys		= []
			for shipping_type, shipping_type of shipping_types
				shipping_types_list_[shipping_type.title]	= """<option value="#{shipping_type.id}">#{shipping_type.title}</option>"""
				keys.push(shipping_type.title)
			keys.sort()
			for key in keys
				shipping_types_list_[key]
		shipping_types_list	= shipping_types_list.join('')
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
		modal			= $.cs.simple_modal("""<form>
			<h3 class="cs-center">#{title}</h3>
			<p class="uk-hidden">
				#{L.shop_datetime}: <span class="date"></span>
			</p>
			<p>
				#{L.shop_user}: <span class="username"></span>, id: <input name="user" required>
			</p>
			<p>
				<div class="items"></div>
				<button type="button" class="add-item uk-button">#{L.shop_add_item}</button>
			</p>
			<p>
				#{L.shop_shipping_type}: <select name="shipping_type" required>#{shipping_types_list}</select>
			</p>
			<p>
				#{L.shop_shipping_phone}: <input name="shipping_phone">
			</p>
			<p>
				#{L.shop_shipping_address}: <textarea name="shipping_address"></textarea>
			</p>
			<p>
				#{L.shop_status}: <select name="status" required>#{order_statuses}</select>
			</p>
			<p>
				#{L.shop_comment}: <textarea name="comment"></textarea>
			</p>
			<p>
				<button class="uk-button" type="submit">#{action}</button>
			</p>
		</form>""", false, 1200)
		do ->
			timeout = 0
			modal.find('[name=user]').keyup ->
				clearTimeout(timeout)
				timeout = setTimeout (=>
					$.getJSON('api/System/profiles/' + $(@).val(), (profile) ->
						modal.find('.username').html(profile.username || profile.login)
					)
				), 300
		do ->
			shipping_type_select	= modal.find('[name=shipping_type]')
			shipping_type_select.change ->
				shipping_type	= shipping_types[$(@).val()]
				modal.find('[name=shipping_phone]').parent()[if parseInt(shipping_type.phone_needed) then 'show' else 'hide']()
				modal.find('[name=shipping_address]').parent()[if parseInt(shipping_type.address_needed) then 'show' else 'hide']()
			shipping_type_select.change()
		do ->
			items_container	= modal.find('.items')
			modal.add_item	= (item) ->
				callback	= (item_data) ->
					total_price	= item_data.price * item.units
					items_container.append("""<p>
						#{L.shop_item}: <input value="-" class="title uk-form-blank" readonly>
						id: <input name="items[item][]" value="#{item.item}" class="uk-form-width-small" required>
						#{L.shop_unit_price} <input name="items[unit_price][]" value="#{item.unit_price}" class="uk-form-width-small" required> (<span class="unit-price">#{item_data.price}</span>)
						#{L.shop_units} <input name="items[units][]" value="#{item.units}" class="uk-form-width-mini" required>
						#{L.shop_total_price} <input name="items[price][]" value="#{item.price}" class="uk-form-width-small" required> (<span class="item-price" data-original-price="#{item_data.price}">#{total_price}</span>)
						<button type="button" class="delete-item uk-button"><i class="uk-icon-close"></i></button>
					</p>""")
					items_container.children(':last').find('.title').val(item_data.title)
				if item.item
					$.getJSON("api/Shop/admin/items/#{item.item}", callback)
				else
					callback(
						title	: '-'
						price	: 0
					)
			timeout	= 0
			items_container
				.on('keyup change', "[name='items[units][]']", ->
					$this					= $(@)
					item_price_container	= $this.parent().find('.item-price')
					item_price_container.html(
						item_price_container.data('original-price') * $this.val()
					)
				)
				.on('keyup', "[name='items[item][]']", ->
					clearTimeout(timeout)
					timeout = setTimeout (=>
						$this		= $(@)
						container	= $this.parent()
						$.ajax(
							url		: 'api/Shop/admin/items/' + $this.val()
							type	: 'get'
							success	: (item) ->
								container.find('.title').val(item.title)
								container.find('.unit-price').html(item.price)
								container.find('.item-price').data('original-price', item.price)
								container.find("[name='items[units][]']").change()
							error	: ->
								container.find('.title').val('-')
								container.find('.unit-price').html(0)
								container.find('.item-price').data('original-price', 0)
								container.find("[name='items[units][]']").change()
						)
					), 300
				)
				.on('click', '.delete-item', ->
					$(@).parent().remove()
				)
		modal
			.on('click', '.add-item', ->
				modal.add_item(
					item		: ''
					unit_price	: ''
					units		: ''
					price		: ''
				)
			)
	$('html')
		.on('mousedown', '.cs-shop-order-add', ->
			$.when(
				$.getJSON('api/Shop/admin/shipping_types')
				$.getJSON('api/Shop/admin/order_statuses')
			).done (shipping_types, order_statuses) ->
				modal = make_modal(shipping_types[0], order_statuses[0], L.shop_order_addition, L.shop_add)
				modal.find('form').submit ->
					data	= $(@).serialize()
					$.ajax(
						url     : 'api/Shop/admin/orders'
						type    : 'post'
						data    : data
						success : (url) ->
							url	= url.split('/')
							$.ajax(
								url     : 'api/Shop/admin/orders/' + url.pop() + '/items'
								type    : 'put'
								data    : data
								success : ->
									alert(L.shop_added_successfully)
									location.reload()
							)
					)
					return false
		)
		.on('mousedown', '.cs-shop-order-edit', ->
			$this		= $(@)
			id			= $this.data('id')
			username	= $this.data('username')
			date		= $this.data('date')
			$.when(
				$.getJSON('api/Shop/admin/shipping_types')
				$.getJSON('api/Shop/admin/order_statuses')
				$.getJSON("api/Shop/admin/orders/#{id}")
				$.getJSON("api/Shop/admin/orders/#{id}/items")
			).done (shipping_types, order_statuses, order, items) ->
				modal	= make_modal(shipping_types[0], order_statuses[0], L.shop_order_edition, L.shop_edit)
				modal.find('form').submit ->
					data	= $(@).serialize()
					$.ajax(
						url     : "api/Shop/admin/orders/#{id}"
						type    : 'put'
						data    : data
						success : ->
							$.ajax(
								url     : "api/Shop/admin/orders/#{id}/items"
								type    : 'put'
								data    : data
								success : ->
									alert(L.shop_edited_successfully)
									location.reload()
							)
					)
					return false
				order	= order[0]
				modal.find('.date').html(date).parent().show()
				modal.find('.username').html(username)
				modal.find('[name=user]').val(order.user)
				modal.find('[name=shipping_phone]').val(order.shipping_phone)
				modal.find('[name=shipping_address]').val(order.shipping_address)
				modal.find('[name=shipping_type]').val(order.shipping_type)
				modal.find('[name=status]').val(order.status)
				modal.find('[name=comment]').val(order.comment)
				items	= items[0]
				items.forEach (item) ->
					modal.add_item(item)
		)
		.on('mousedown', '.cs-shop-order-delete', ->
			id = $(@).data('id')
			if confirm(L.shop_sure_want_to_delete)
				$.ajax(
					url     : "api/Shop/admin/orders/#{id}"
					type    : 'delete'
					success : ->
						alert(L.shop_deleted_successfully)
						location.reload()
				)
		)
