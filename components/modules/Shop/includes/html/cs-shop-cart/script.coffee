###*
 * @package       Shop
 * @order_status  modules
 * @author        Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright     Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license       MIT License, see license.txt
###
L	= cs.Language
Polymer(
	shipping_types				: cs.shop.shipping_types
	shipping_type				: 0
	shipping_type_details		: {}
	shipping_type_text			: L.shop_shipping_type
	shipping_cost_formatted		: ''
	shipping_username_text		: L.shop_shipping_username
	phone_text					: L.shop_shipping_phone
	address_text				: L.shop_shipping_address
	comment_text				: L.shop_comment
	payment_method_text			: L.shop_payment_method
	finish_order_text			: L.shop_finish_order
	shipping_username			: localStorage.shipping_username || ''
	phone						: localStorage.phone || ''
	address						: localStorage.address || ''
	comment						: localStorage.comment || ''
	payment_method				: 0
	payment_methods				:
		for method, details of cs.shop.payment_methods
			details.method	= method
			details
	created						: ->
		@shipping_username			= @shipping_username || (if cs.is_user then @getAttribute('username') else '')
	domReady						: ->
		@$.h1.innerHTML	= @querySelector('h1').innerHTML
		$(@shadowRoot).find('textarea').autosize()
		$shipping_type			= $(@$.shipping_type)
		$shipping_type
			.val(@shipping_types[0].id)
			.change =>
				@shipping_types.forEach (shipping_type) =>
					if shipping_type.id == $shipping_type.val()
						@shipping_type_details		= shipping_type
						@shipping_cost_formatted	= sprintf(cs.shop.settings.price_formatting, shipping_type.price)
						return false
			.change()
		$payment_method			= $(@$.payment_method)
		$payment_method
			.val(@payment_method)
			.change =>
				payment_method							= @payment_methods[$payment_method.val()]
				@payment_method							= payment_method.method
				@$.payment_method_description.innerHTML	= payment_method.description
			.change()
	shipping_usernameChanged	: ->
		localStorage.shipping_username	= @shipping_username
	phoneChanged				: ->
		localStorage.phone	= @phone
	addressChanged				: ->
		localStorage.address	= @address
	commentChanged				: ->
		localStorage.comment	= @comment
	finish_order				: ->
		$.ajax(
			url		: 'api/Shop/orders'
			type	: 'post'
			data	:
				shipping_type		: @shipping_type
				shipping_username	: @shipping_username
				shipping_phone		: if @shipping_type_details.phone_needed then @phone else ''
				shipping_address	: if @shipping_type_details.address_needed then @address else ''
				payment_method		: @payment_method
				comment				: @comment
				items				: cs.shop.cart.get_all()
			success	: (result) =>
				cs.shop.cart.clean()
				if @payment_method == 'shop:cash' # Default payment method (Orders::PAYMENT_METHOD_CASH)
					$.cs.simple_modal("""
						<h1 class="uk-text-center">#{L.shop_thanks_for_order}</h1>
					""")
					.on 'hide.uk.modal', ->
						location.href	= 'Shop/orders_'
				else
					id		= result.split('/').pop()
					modal	= $.cs.simple_modal("""
						<h1 class="uk-text-center">#{L.shop_thanks_for_order}</h1>
						<p class="uk-text-center">
							<button type="button" class="uk-button uk-button-primary pay-now">#{L.shop_pay_now}</button>
							<button type="button" class="uk-button pay-later">#{L.shop_pay_later}</button>
						</p>
					""")
					.on 'hide.uk.modal', ->
						location.href	= 'Shop/orders_'
					modal.find('.pay-now').click ->
						location.href	= "Shop/pay/#{id}"
					modal.find('.pay-later').click ->
						modal.hide()
		)
);
