/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	is			: 'cs-shop-order-item'
	properties	:
		item_id		: Number
		price		: Number
		unit_price	: Number
		units		: Number
	ready : !->
		img							= @querySelector('#img')
		@$.img.src					= img.src
		@$.img.title				= img.title
		@$.description.innerHTML	= @querySelector('#description').innerHTML
		href = @querySelector('#link').href
		if href
			@$['img-link'].href	= href
			@$.link.href		= href
		@item_title				= @querySelector('#link').innerHTML
		Promise.all([
			cs.Language('shop_').ready()
			require(['sprintf-js'])
		]).then ([L, [{sprintf}]]) !~>
			@unit_price_formatted	= sprintf(cs.shop.settings.price_formatting, @unit_price)
			@price_formatted		= sprintf(cs.shop.settings.price_formatting, @price)
			discount				= @units * @unit_price - @price
			if discount
				discount				= sprintf(cs.shop.settings.price_formatting, discount)
				@$.discount.textContent	= "(#{L.discount}: #{discount})"
);
