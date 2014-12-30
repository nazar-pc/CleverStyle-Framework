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
	shipping_type				: cs.shop.shipping_types[0].id
	shipping_type_description	: ''
	shipping_type_text			: L.shop_shipping_type
	shipping_cost				: 0
	shipping_cost_formatted		: ''
	ready						: ->
		@$.h1.innerHTML	= @querySelector('h1').innerHTML
		@shipping_typeChanged()
	shipping_typeChanged		: ->
		@shipping_types.forEach (shipping_type) =>
			if shipping_type.id == @shipping_type
				@shipping_type_description		= shipping_type.description
				@shipping_cost					= shipping_type.price
				@shipping_cost_formatted		= sprintf(cs.shop.settings.price_formatting, @shipping_cost)
				return false
);
