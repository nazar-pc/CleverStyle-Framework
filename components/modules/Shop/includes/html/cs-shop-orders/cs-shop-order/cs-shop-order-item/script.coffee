###*
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
Polymer(
	ready : ->
		@$.img.innerHTML		= @querySelector('#img').outerHTML
		href					= @querySelector('#link').href
		if href
			@$.img.href		= href
			@$.link.href	= href
		@item_title				= @querySelector('#link').innerHTML
		$this					= $(@)
		unit_price				= $this.data('unit-price')
		price					= $this.data('price')
		@units					= $this.data('units')
		@unit_price_formatted	= sprintf(cs.shop.settings.price_formatting, unit_price)
		@price_formatted		= sprintf(cs.shop.settings.price_formatting, price)
		discount				= @units * unit_price - price
		if discount
			discount				= sprintf(cs.shop.settings.price_formatting, discount)
			@$.discount.innerHTML	= "(#{cs.Language.shop_discount}: #{discount})"
);
