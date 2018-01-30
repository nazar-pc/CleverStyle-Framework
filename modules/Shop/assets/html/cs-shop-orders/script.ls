/**
 * @package  Shop
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
Polymer(
	is			: 'cs-shop-orders'
	behaviors	: [
		cs.Polymer.behaviors.Language('shop_')
	]
	ready : !->
		@$.orders.innerHTML	= @querySelector('#orders').innerHTML
)
