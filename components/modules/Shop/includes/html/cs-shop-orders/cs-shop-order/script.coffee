###*
 * @package       Shop
 * @order_status  modules
 * @author        Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright     Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license       MIT License, see license.txt
###
L	= cs.Language
Polymer(
	shipping_type_text	: L.shop_shipping_type
	total_price_text	: L.shop_total_price
	discount_text		: L.shop_discount
	shipping_cost_text	: L.shop_shipping_cost
	for_payment_text	: L.shop_for_payment
	phone_text			: L.shop_shipping_phone
	address_text		: L.shop_shipping_address
	pay_now_text		: L.shop_pay_now
	paid				: true
	ready				: ->
		$this						= $(@)
		@paid						= $this.data('paid')
		@order_number				= sprintf(L.shop_order_number, $this.data('id'))
		@order_date					= $this.data('date-formatted')
		@order_status				= $this.children('#order_status').text()
		shipping_type				= $this.children('#shipping_type')
		@shipping_type				= shipping_type.text()
		@shipping_cost				= $this.data('shipping_cost')
		@shipping_cost_formatted	= sprintf(cs.shop.settings.price_formatting, @shipping_cost)
		total_price					= 0
		discount					= 0
		$this.find('cs-shop-order-item').each ->
			$item		= $(@)
			units		= $item.data('units')
			unit_price	= $item.data('unit-price')
			price		= $item.data('price')
			total_price	+= units * unit_price
			discount	+= (units * unit_price) - price
		@total_price_formatted	= sprintf(cs.shop.settings.price_formatting, total_price)
		@discount_formatted		= if discount then sprintf(cs.shop.settings.price_formatting, discount) else ''
		@for_payment_formatted	= sprintf(cs.shop.settings.price_formatting, $this.data('for_payment'))
		@phone					= @querySelector('#phone')?.innerHTML || ''
		@address				= $.trim(@querySelector('#address')?.innerHTML || '').replace(/\n/g, '<br>')
		@comment				= $.trim(@querySelector('#comment')?.innerHTML || '').replace(/\n/g, '<br>')
	phoneChanged		: ->
		@$.phone.innerHTML	= @phone
	addressChanged		: ->
		@$.address.innerHTML	= @address
	commentChanged		: ->
		@$.comment.innerHTML	= @comment
	pay					: ->
		location.href	= 'Shop/pay/' + $(@).data('id')
);
