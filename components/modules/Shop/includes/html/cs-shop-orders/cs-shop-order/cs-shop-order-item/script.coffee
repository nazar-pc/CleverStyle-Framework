###*
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
Polymer(
	'is'		: 'cs-shop-order-item'
	properties	:
		item_id		: Number
		price		: Number
		unit_price	: Number
		units		: Number
	ready		: ->
		do (img = @querySelector('#img')) =>
			@$.img.src		= img.src
			@$.img.title	= img.title
		href = @querySelector('#link').href
		if href
			@$['img-link'].href	= href
			@$.link.href		= href
		@item_title				= @querySelector('#link').innerHTML
		@unit_price_formatted	= sprintf(cs.shop.settings.price_formatting, @unit_price)
		@price_formatted		= sprintf(cs.shop.settings.price_formatting, @price)
		discount				= @units * @unit_price - @price
		if discount
			discount				= sprintf(cs.shop.settings.price_formatting, discount)
			@$.discount.textContent	= "(#{cs.Language.shop_discount}: #{discount})"
);
