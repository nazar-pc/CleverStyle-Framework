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
	phone_text					: L.shop_shipping_phone
	address_text				: L.shop_shipping_address
	created						: ->
		@shipping_type_details	= @shipping_types[0]
		@shipping_type			= @shipping_type_details.id
	ready						: ->
		@$.h1.innerHTML	= @querySelector('h1').innerHTML
		$(@$.address).autosize()
		@shipping_typeChanged()
	shipping_typeChanged		: ->
		@shipping_types.forEach (shipping_type) =>
			if shipping_type.id == @shipping_type
				@shipping_type_details		= shipping_type
				@shipping_cost_formatted	= sprintf(cs.shop.settings.price_formatting, shipping_type.price)
				return false
);
