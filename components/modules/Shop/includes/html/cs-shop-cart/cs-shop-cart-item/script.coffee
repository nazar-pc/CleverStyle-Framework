###*
 * @package       Shop
 * @order_status  modules
 * @author        Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright     Copyright (c) 2014, Nazar Mokrynskyi
 * @license       MIT License, see license.txt
###
cart	= cs.shop.cart
Polymer(
	units			: 0
	ready			: ->
		@$.img.innerHTML		= @querySelector('#img').outerHTML
		@href					= @querySelector('#link').href
		@item_title				= @querySelector('#link').innerHTML
		$this					= $(@)
		@item_id				= $this.data('id')
		@unit_price				= $this.data('unit-price')
		@price					= $this.data('price')
		@units					= $this.data('units')
		@unit_price_formatted	= sprintf(cs.shop.settings.price_formatting, @unit_price)
		@price_formatted		= sprintf(cs.shop.settings.price_formatting, @price)
	unitsChanged	: ->
		if parseInt(@units)
			cart.set(@item_id, @units)
		else
			console.log @units
			cart.del(@item_id)
		@price				= @unit_price * @units # TODO discount feature
		@price_formatted	= sprintf(cs.shop.settings.price_formatting, @price)
		discount			= @units * @unit_price - @price
		@$.discount.innerHTML	=
			if discount
				discount	= sprintf(cs.shop.settings.price_formatting, discount)
				"(#{cs.Language.shop_discount}: #{discount})"
			else
				''
);
