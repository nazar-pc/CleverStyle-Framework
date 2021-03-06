/**
 * @package  Shop
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
cart				= cs.shop.cart
price_formatting	= cs.shop.settings.price_formatting
Polymer(
	is			: 'cs-shop-cart-item'
	properties	:
		item_id					: Number
		unit_price				: Number
		units					: Number
		href					: String
		item_title				: String
		unit_price_formatted	: String
		price_formatted			: String
	observers	: [
		'units_changed(item_id, units)'
	]
	attached : !->
		let (img = @querySelector('#img'))
			@$.img.src		= img.src
			@$.img.title	= img.title
		@$.description.innerHTML	= @querySelector('#description').innerHTML
		link						= @querySelector('#link')
		@href						= link.href
		@item_title					= link.textContent
		{sprintf} <~! require(['sprintf-js'])
		@unit_price_formatted	= sprintf(price_formatting, @unit_price)
	units_changed : (item_id, units) !->
		if !item_id || units == undefined
			return
		if parseInt(units)
			cart.set(item_id, units)
		else
			cart.del(item_id)
			@recalculate(0, 0)
			return
		clearTimeout(@_recalculate_interval)
		@_recalculate_interval	= setTimeout (!~>
			cart.get_calculated (data) !~>
				data.items.forEach (item) !~>
					if parseInt(item.id) ~= item_id
						@recalculate(item.price, units)
						return false
		), (if !@price_formatted then 0 else 100) # To do it faster for the first time
	recalculate : (price, units) !->
		{sprintf} <~! require(['sprintf-js'])
		@price_formatted	= sprintf(price_formatting, price)
		discount			= units * @unit_price - price
		cs.Language('shop_').ready().then (L) !~>
			@$.discount.textContent	=
				if discount
					discount	= sprintf(price_formatting, discount)
					"(#{L.discount}: #{discount})"
				else
					''
);
