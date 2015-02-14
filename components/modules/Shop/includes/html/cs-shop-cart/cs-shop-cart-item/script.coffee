###*
 * @package       Shop
 * @order_status  modules
 * @author        Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright     Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license       MIT License, see license.txt
###
cart				= cs.shop.cart
price_formatting	= cs.shop.settings.price_formatting
Polymer(
	units			: 0
	ready			: ->
		@$.img.innerHTML		= @querySelector('#img').outerHTML
		@href					= @querySelector('#link').href
		@item_title				= @querySelector('#link').innerHTML
		$this					= $(@)
		@item_id				= $this.data('id')
		@unit_price				= $this.data('unit-price')
		@price					= 0
		@units					= $this.data('units')
		@unit_price_formatted	= sprintf(price_formatting, @unit_price)
		@price_formatted		= sprintf(price_formatting, @price)
	unitsChanged	: ->
		if parseInt(@units)
			cart.set(@item_id, @units)
		else
			cart.del(@item_id)
		cart.get_calculated (data) =>
			data.items.forEach (item) =>
				if parseInt(item.id) == @item_id
					@price				= item.price
					@price_formatted	= sprintf(price_formatting, @price)
					discount			= @units * @unit_price - @price
					@$.discount.innerHTML	=
						if discount
							discount	= sprintf(price_formatting, discount)
							"(#{cs.Language.shop_discount}: #{discount})"
						else
							''
					return false
			return
		return
);
