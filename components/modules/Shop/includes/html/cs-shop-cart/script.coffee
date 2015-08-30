###*
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
L		= cs.Language
shop	= cs.shop
cart	= shop.cart
params	= cart.params
Polymer(
	'is'						: 'cs-shop-cart'
	behaviors					: [cs.Polymer.behaviors.Language]
	properties					:
		shipping_types			: []
		shipping_type			:
			observer	: 'shipping_type_changed'
			type		: Number
			value		: params.shipping_type
		shipping_type_details	: Object
		shipping_cost_formatted	: ''
		shipping_username		:
			observer	: 'shipping_username_changed'
			type		: String
			value		: params.shipping_username || ''
		phone					:
			observer	: 'phone_changed'
			type		: String
			value		: params.phone || ''
		address					:
			observer	: 'address_changed'
			type		: String
			value		: params.address || ''
		comment					:
			observer	: 'comment_changed'
			type		: String
			value		: params.comment || ''
		payment_method			:
			observer	: 'payment_method_changed'
			type		: String
		payment_methods			: []
		registration_required	: !cs.is_user && !shop.settings.allow_guests_orders
	ready						: ->
		@set('shipping_types', shop.shipping_types)
		@set(
			'payment_methods'
			for method, details of shop.payment_methods
				details.method	= method
				details
		)
		@shipping_type	= @shipping_type || shop.shipping_types[0].id
		@payment_method	= @payment_methods[0].method
		@set('shipping_username', @shipping_username || (if cs.is_user then @getAttribute('username') else ''))
		autosize(@shadowRoot.querySelectorAll('textarea'))
	shipping_type_changed		: (shipping_type_selected) ->
		params.shipping_type	= shipping_type_selected
		shop.shipping_types.forEach (shipping_type) =>
			if shipping_type.id == shipping_type_selected
				@set('shipping_type_details', shipping_type)
				@set('shipping_cost_formatted', sprintf(shop.settings.price_formatting, shipping_type.price))
				return false
	payment_method_changed		: (payment_method_selected) ->
		@$.payment_method_description.innerHTML	= shop.payment_methods[payment_method_selected].description
	shipping_username_changed	: ->
		params.shipping_username	= @shipping_username
	phone_changed				: ->
		params.phone	= @phone
	address_changed				: ->
		params.address	= @address
	comment_changed				: ->
		params.comment	= @comment
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
				items				: cart.get_all()
			success	: (result) =>
				cart.clean()
				if @payment_method == 'shop:cash' # Default payment method (Orders::PAYMENT_METHOD_CASH)
					$(
						cs.ui.simple_modal("""
							<h1 class="cs-text-center">#{L.shop_thanks_for_order}</h1>
						""")
					).on('close', ->
						location.href	= 'Shop/orders_'
					)
				else
					id		= result.split('/').pop()
					modal	= $(cs.ui.simple_modal("""
						<h1 class="cs-text-center">#{L.shop_thanks_for_order}</h1>
						<p class="cs-text-center">
							<button is="cs-button" primary type="button" class="pay-now">#{L.shop_pay_now}</button>
							<button is="cs-button" type="button" class="pay-later">#{L.shop_pay_later}</button>
						</p>
					""")).on('close', ->
						location.href	= 'Shop/orders_'
					)
					modal.find('.pay-now').click ->
						location.href	= "Shop/pay/#{id}"
					modal.find('.pay-later').click ->
						modal.hide()
						location.href	= 'Shop/orders_'
		)
);
