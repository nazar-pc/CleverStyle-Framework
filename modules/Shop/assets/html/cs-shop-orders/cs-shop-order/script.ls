/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	is			: 'cs-shop-order'
	behaviors	: [
		cs.Polymer.behaviors.Language('shop_')
	]
	properties	:
		order_id			: Number
		date				: Number
		date_formatted		: String
		shipping_cost		: Number
		for_payment			: Number
		payment_method		: String
		paid				: Boolean
	ready : !->
		Promise.all([
			require(['sprintf-js'])
			cs.Language.ready()
		]).then ([[{sprintf}]]) !~>
			@show_pay_now				= !@paid && @payment_method != 'shop:cash'
			@order_number	= sprintf('' + @L.order_number, @order_id)
			@order_status				= @querySelector('#order_status').textContent
			@shipping_type				= @querySelector('#shipping_type').textContent
			@shipping_cost_formatted	= sprintf(cs.shop.settings.price_formatting, @shipping_cost)
			total_price					= 0
			discount					= 0
			for item in @querySelectorAll('cs-shop-order-item')
				# TODO calling properties doesn't work in Firefox for some reason
				total_price	+= item.getAttribute('units') * item.getAttribute('unit_price')
				discount	+= (item.getAttribute('units') * item.getAttribute('unit_price')) - item.getAttribute('price')
			@total_price_formatted	= sprintf(cs.shop.settings.price_formatting, total_price)
			@discount_formatted		= if discount then sprintf(cs.shop.settings.price_formatting, discount) else ''
			@for_payment_formatted	= sprintf(cs.shop.settings.price_formatting, @for_payment)
			@phone					= @querySelector('#phone')?.textContent || ''
			@$.phone.textContent	= @phone
			@address				= $.trim(@querySelector('#address')?.textContent || '').replace(/\n/g, '<br>')
			@$.address.textContent	= @address
			@comment				= $.trim(@querySelector('#comment')?.textContent || '').replace(/\n/g, '<br>')
			@$.comment.textContent	= @comment
	pay : !->
		location.href	= 'Shop/pay/' + @order_id
);
