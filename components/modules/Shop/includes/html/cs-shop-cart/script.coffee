###*
 * @package       Shop
 * @order_status  modules
 * @author        Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright     Copyright (c) 2014, Nazar Mokrynskyi
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
	finish_order_text			: L.shop_finish_order
	shipping_username			: localStorage.shipping_username || ''
	phone						: localStorage.phone || ''
	address						: localStorage.address || ''
	comment						: localStorage.comment || ''
	created						: ->
		@shipping_type_details	= @shipping_types[0]
		@shipping_type			= @shipping_type_details.id
		@shipping_username		= @shipping_username || (if cs.is_user then @getAttribute('username') else '')
	ready						: ->
		@$.h1.innerHTML	= @querySelector('h1').innerHTML
		$(@shadowRoot).find('textarea').autosize()
		@shipping_typeChanged()
	shipping_typeChanged		: ->
		@shipping_types.forEach (shipping_type) =>
			if shipping_type.id == @shipping_type
				@shipping_type_details		= shipping_type
				@shipping_cost_formatted	= sprintf(cs.shop.settings.price_formatting, shipping_type.price)
				return false
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
				comment				: @comment
				items				: cs.shop.cart.get_all()
			success	: ->
				$.cs.simple_modal("""
					<h1 class="uk-text-center">#{L.shop_thanks_for_order}</h1>
				""")
				.on 'hide.uk.modal', ->
					cs.shop.cart.clean()
					location.href	= 'Shop/orders_'
		)
);
