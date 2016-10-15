/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
const GUEST_ID		= 1
const DEFAULT_IMAGE	= '/modules/Shop/assets/img/no-image.svg'
shop	= cs.shop
cart	= shop.cart
params	= cart.params
Polymer(
	is			: 'cs-shop-cart'
	behaviors	: [
		cs.Polymer.behaviors.Language('shop_')
	]
	properties	:
		items					: Array
		shipping_types			:
			type	: Array
			# TODO: Load this through API, not include on page
			value	: shop?.shipping_types
		shipping_type			:
			observer	: 'shipping_type_changed'
			type		: Number
			value		: params.shipping_type || shop?.shipping_types[0].id
		shipping_type_details	: Object
		shipping_cost_formatted	: ''
		shipping_username		:
			observer	: 'shipping_username_changed'
			type		: String
			value		: params.shipping_username
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
		payment_methods			:
			for method, details of shop.payment_methods
				details.method	= method
				details
		registration_required	: Boolean
	ready : !->
		@payment_method	= @payment_methods[0].method
		Promise.all([
			...for let item_id, units of cart.get_all()
				cs.api("get api/Shop/items/#item_id").then (data) ->
					{
						units	: units
						image	: data.images[0] || DEFAULT_IMAGE
					} <<<< data
			cs.api('get api/System/profile')
		]).then ([...@items, profile]) !~>
			is_user					= profile.id != GUEST_ID
			@registration_required	= !shop.settings.allow_guests_orders && !is_user
			if !@shipping_username && is_user
				@shipping_username = data.username || data.login
	shipping_type_changed : (shipping_type_selected) !->
		{sprintf} <~! require(['sprintf-js'])
		params.shipping_type	= shipping_type_selected
		shop.shipping_types.forEach (shipping_type) !~>
			if shipping_type.id ~= shipping_type_selected
				@set('shipping_type_details', shipping_type)
				@set('shipping_cost_formatted', sprintf(shop.settings.price_formatting, shipping_type.price))
				return false
	payment_method_changed : (payment_method_selected) !->
		if (payment_method_selected)
			@$.payment_method_description.innerHTML	= shop.payment_methods[payment_method_selected].description
	shipping_username_changed : !->
		params.shipping_username	= @shipping_username
	phone_changed : !->
		params.phone	= @phone
	address_changed : !->
		params.address	= @address
	comment_changed : !->
		params.comment	= @comment
	finish_order : !->
		if !@shipping_username
			cs.ui.alert(@L.shipping_username_is_required)
			return
		data	=
			shipping_type		: @shipping_type
			shipping_username	: @shipping_username
			shipping_phone		: if @shipping_type_details.phone_needed then @phone else ''
			shipping_address	: if @shipping_type_details.address_needed then @address else ''
			payment_method		: @payment_method
			comment				: @comment
			items				: cart.get_all()
		cs.api('post api/Shop/orders', data).then (result) !~>
			cart.clean()
			if @payment_method == 'shop:cash' # Default payment method (Orders::PAYMENT_METHOD_CASH)
				cs.ui.simple_modal("""
					<h1 class="cs-text-center">#{@L.thanks_for_order}</h1>
				""").addEventListener('close', !->
					location.href	= 'Shop/orders_'
				)
			else
				id		= result.split('/').pop()
				modal	= cs.ui.simple_modal("""
					<h1 class="cs-text-center">#{@L.thanks_for_order}</h1>
					<p class="cs-text-center">
						<button is="cs-button" primary type="button" class="pay-now">#{@L.pay_now}</button>
						<button is="cs-button" type="button" class="pay-later">#{@L.pay_later}</button>
					</p>
				""")
				modal.addEventListener('close', !->
					location.href	= 'Shop/orders_'
				)
				modal.querySelector('.pay-now').addEventListener('click', !->
					location.href	= "Shop/pay/#id"
				)
				modal.querySelector('.pay-later').addEventListener('click', !->
					location.href	= 'Shop/orders_'
				)
);
