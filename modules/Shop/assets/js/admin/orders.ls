/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
$ <-! require(['jquery'], _)
<-! $
make_modal	= (shipping_types, order_statuses, payment_methods, L, title, action) ->
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
	order_statuses			= order_statuses.join('')
	payment_methods_list	=
		for method, details of payment_methods
			"""<option value="#method">#{details.title}</option>"""
	payment_methods_list	= payment_methods_list.join('')
	modal					= $(cs.ui.simple_modal("""<form>
		<h3 class="cs-text-center">#title</h3>
		<p hidden>
			#{L.datetime}: <span class="date"></span>
		</p>
		<p>
			#{L.user}: <span class="username"></span>, id: <cs-input-text><input compact name="user" required></cs-input-text>
		</p>
		<p>
			<div class="items"></div>
			<button is="cs-button" class="add-item">#{L.add_item}</button>
		</p>
		<p>
			#{L.shipping_type}: <select is="cs-select" name="shipping_type" required>#shipping_types_list</select>
		</p>
		<p>
			#{L.shipping_cost}: <cs-input-text><input name="shipping_cost"></cs-input-text> (<span id="shipping_cost"></span>)
		</p>
		<p>
			#{L.shipping_username}: <cs-input-text><input name="shipping_username"></cs-input-text>
		</p>
		<p>
			#{L.shipping_phone}: <cs-input-text><input name="shipping_phone"></cs-input-text>
		</p>
		<p>
			#{L.shipping_address}: <textarea is="cs-textarea" autosize name="shipping_address"></textarea>
		</p>
		<p>
			#{L.payment_method}: <select is="cs-select" name="payment_method" required>#payment_methods_list</select>
		</p>
		<p>
			#{L.paid}:
			<label is="cs-label-button"><input type="radio" name="paid" value="1"> #{L.yes}</label>
			<label is="cs-label-button"><input type="radio" name="paid" value="0" checked> #{L.no}</label>
		</p>
		<p>
			#{L.status}: <select is="cs-select" name="status" required>#order_statuses</select>
		</p>
		<p>
			#{L.comment}: <textarea is="cs-textarea" autosize name="comment"></textarea>
		</p>
		<p>
			<button is="cs-button" primary type="submit">#action</button>
		</p>
	</form>"""))
	do !->
		timeout = 0
		modal.find('[name=user]').keyup !->
			clearTimeout(timeout)
			timeout := setTimeout (!~>
				cs.api('get api/System/profiles/' + $(@).val()).then (profile) !->
					modal.find('.username').html(profile.username || profile.login)
			), 300
	do !->
		shipping_type_select	= modal.find('[name=shipping_type]')
		shipping_type_select.change !->
			shipping_type	= shipping_types[$(@).val()]
			modal.find('[name=shipping_phone]').parent()[if parseInt(shipping_type.phone_needed) then 'show' else 'hide']()
			modal.find('[name=shipping_address]').parent()[if parseInt(shipping_type.address_needed) then 'show' else 'hide']()
			modal.find('#shipping_cost').html(shipping_type.price)
		shipping_type_select.change()
	do !->
		items_container	= modal.find('.items')
		modal.add_item	= (item) !->
			callback	= (item_data) !->
				total_price	= item_data.price * item.units
				items_container.append("""<p>
					#{L.item}: <cs-input-text compact><input value="-" class="title" readonly></cs-input-text>
					id: <cs-input-text compact><input name="items[item][]" value="#{item.item}" required></cs-input-text>
					#{L.unit_price} <cs-input-text compact><input name="items[unit_price][]" value="#{item.unit_price}" required></cs-input-text> (<span class="unit-price">#{item_data.price}</span>)
					#{L.units} <cs-input-text compact><input name="items[units][]" value="#{item.units}" required></cs-input-text>
					#{L.total_price} <cs-input-text compact><input name="items[price][]" value="#{item.price}" required></cs-input-text> (<span class="item-price" data-original-price="#{item_data.price}">#total_price</span>)
					<button is="cs-button" icon="close" type="button" class="delete-item"></button>
				</p>""")
				items_container.children(':last').find('.title').val(item_data.title)
			if item.item
				cs.api("get api/Shop/admin/items/#{item.item}").then(callback)
			else
				callback(
					title	: '-'
					price	: 0
				)
		timeout	= 0
		items_container
			.on('keyup change', "[name='items[units][]']", !->
				$this					= $(@)
				item_price_container	= $this.parent().find('.item-price')
				item_price_container.html(
					item_price_container.data('original-price') * $this.val()
				)
			)
			.on('keyup', "[name='items[item][]']", !->
				clearTimeout(timeout)
				timeout := setTimeout (!~>
					$this		= $(@)
					container	= $this.parent()
					cs.api('get api/Shop/admin/items/' + $this.val())
						.then (item) !->
							container.find('.title').val(item.title)
							container.find('.unit-price').html(item.price)
							container.find('.item-price').data('original-price', item.price)
							container.find("[name='items[units][]']").change()
						.catch (o) !->
							clearTimeout(o.timeout)
							container.find('.title').val('-')
							container.find('.unit-price').html(0)
							container.find('.item-price').data('original-price', 0)
							container.find("[name='items[units][]']").change()
				), 300
			)
			.on('click', '.delete-item', !->
				$(@).parent().remove()
			)
	modal
		.on('click', '.add-item', !->
			modal.add_item(
				item		: ''
				unit_price	: ''
				units		: ''
				price		: ''
			)
		)
$('html')
	.on('mousedown', '.cs-shop-order-add', !->
		Promise.all([
			cs.api([
				'get api/Shop/admin/shipping_types'
				'get api/Shop/admin/order_statuses'
				'get api/Shop/payment_methods'
			])
			cs.Language('shop_').ready()
		]).then ([[shipping_types, order_statuses, payment_methods], L]) !->
			modal = make_modal(shipping_types, order_statuses, payment_methods, L, L.order_addition, L.add)
			modal.find('form').submit ->
				cs.api('post api/Shop/admin/orders', @)
					.then (url) -> url.split('/')
					.then (url) ~> cs.api('put api/Shop/admin/orders/' + url.pop() + '/items', @)
					.then -> cs.ui.alert(L.added_successfully)
					.then(location~reload)
				false
	)
	.on('mousedown', '.cs-shop-order-statuses-history', !->
		id	= $(@).data('id')
		cs.api([
			'get api/Shop/admin/order_statuses'
			"get api/Shop/admin/orders/#id/statuses"
		]).then ([order_statuses, statuses]) !->
			order_statuses	= do ->
				result	= {}
				order_statuses.forEach (status) !->
					result[status.id]	= status
				result
			content			= ''
			statuses.forEach (status) !->
				order_status	= order_statuses?[status.status]
				color			=
					if order_status
						"background: #{order_status.color}"
					else
						''
				comment			=
					if status.comment
						"""
							<tr style="#color">
								<td colspan="2" style="white-space:pre">#{status.comment}</td>
							</tr>
						"""
					else
						''
				content			+= """
					<tr style="#color">
						<td><cs-icon icon="calendar"></cs-icon> #{status.date_formatted}</td>
						<td>#{order_status?.title}</td>
					</tr>
					#comment
				"""
			cs.ui.simple_modal("""
				<table class="cs-table" list>#content</table>
			""")
	)
	.on('mousedown', '.cs-shop-order-edit', !->
		$this		= $(@)
		id			= $this.data('id')
		username	= $this.data('username')
		date		= $this.data('date')
		Primise.all([
			cs.api([
				'get api/Shop/admin/shipping_types'
				'get api/Shop/admin/order_statuses'
				'get api/Shop/payment_methods'
				"get api/Shop/admin/orders/#id"
				"get api/Shop/admin/orders/#id/items"
			])
		]).then ([[shipping_types, order_statuses, payment_methods, order, items], L]) !->
			modal	= make_modal(shipping_types, order_statuses, payment_methods, L, L.order_edition, L.edit)
			modal.find('form').submit ->
				cs.api("put api/Shop/admin/orders/#id", @)
					.then ~> cs.api("put api/Shop/admin/orders/#id/items", @)
					.then -> cs.ui.alert(L.edited_successfully)
					.then(location~reload)
				false
			modal.find('.date').html(date).parent().removeAttr('hidden')
			modal.find('.username').html(username)
			modal.find('[name=user]').val(order.user)
			modal.find('[name=shipping_phone]').val(order.shipping_phone)
			modal.find('[name=shipping_address]').val(order.shipping_address)
			modal.find('[name=shipping_type]').val(order.shipping_type).change()
			modal.find('[name=shipping_cost]').val(order.shipping_cost).change()
			modal.find('[name=shipping_username]').val(order.shipping_username).change()
			modal.find('[name=payment_method]').val(order.payment_method)
			modal.find('[name=paid][value=' + (if parseInt(order.paid) then 1 else 0) + ']').prop('checked', true)
			modal.find('[name=status]').val(order.status)
			modal.find('[name=comment]').val(order.comment)
			items.forEach (item) !->
				modal.add_item(item)
	)
	.on('mousedown', '.cs-shop-order-delete', !->
		id = $(@).data('id')
		cs.Language('shop_').ready().then (L) !->
			cs.ui.confirm(L.sure_want_to_delete)
				.then -> cs.api("delete api/Shop/admin/orders/#id")
				.then -> cs.ui.alert(L.deleted_successfully)
				.then(location~reload)
	)
