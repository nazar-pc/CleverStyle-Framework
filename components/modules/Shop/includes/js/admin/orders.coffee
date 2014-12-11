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
			shipping_types_	= {}
			keys		= []
			for shipping_type, shipping_type of shipping_types
				shipping_types_[shipping_type.title]	= """<option value="#{shipping_type.id}">#{shipping_type.title}</option>"""
				keys.push(shipping_type.title)
			keys.sort()
			for key in keys
				shipping_types_[key]
		shipping_types	= shipping_types.join('')
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
				#{L.shop_datetime}: <span class="date"></span>
			</p>
			<p>
				#{L.shop_user}: <span class="user"></span>, id: <input name="user">
			</p>
			<p>
				#{L.shop_shipping_phone}: <input name="shipping_phone">
			</p>
			<p>
				#{L.shop_shipping_address}: <textarea name="shipping_address"></textarea>
			</p>
			<p>
				#{L.shop_shipping_type}: <select name="shipping_type" required>#{shipping_types}</select>
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
		</form>""")
	$('html')
		.on('mousedown', '.cs-shop-order-add', ->
			$.when(
				$.getJSON('api/Shop/admin/shipping_types')
				$.getJSON('api/Shop/admin/order_statuses')
			).done (shipping_types, order_statuses) ->
				modal = make_modal(shipping_types[0], order_statuses[0], L.shop_order_addition, L.shop_add)
				modal.find('form').submit ->
					$.ajax(
						url     : 'api/Shop/admin/orders'
						type    : 'post'
						data    : $(@).serialize()
						success : ->
							alert(L.shop_added_successfully)
							location.reload()
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
			).done (shipping_types, order_statuses, order) ->
				modal = make_modal(shipping_types[0], order_statuses[0], L.shop_order_edition, L.shop_edit)
				modal.find('form').submit ->
					$.ajax(
						url     : "api/Shop/admin/orders/#{id}"
						type    : 'put'
						data    : $(@).serialize()
						success : ->
							alert(L.shop_edited_successfully)
							location.reload()
					)
					return false
				order	= order[0]
				modal.find('.date').html(date)
				modal.find('.user').html(username)
				modal.find('[name=user]').val(order.user)
				modal.find('[name=shipping_phone]').val(order.shipping_phone)
				modal.find('[name=shipping_address]').val(order.shipping_address)
				modal.find('[name=shipping_type]').val(order.shipping_type)
				modal.find('[name=status]').val(order.status)
				modal.find('[name=comment]').val(order.comment)
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
