/**
 * @package  Shop
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
cs.shop.cart	= do ->
	items_storage	=
		get	: ->
			JSON.parse(localStorage.shop_cart_items || '{}')
		set	: (items) !->
			localStorage.shop_cart_items	= JSON.stringify(items)
	params			= do ->
		params_			= localStorage.shop_cart_params
		params_			= if params_ then JSON.parse(params_) else {}
		update_params	= !->
			localStorage.shop_cart_params = JSON.stringify(params_)
		{
			shipping_type:~
				-> params_.shipping_type
				(val) !->
					params_.shipping_type = val
					update_params()
			shipping_username:~
				-> params_.shipping_username
				(val) !->
					params_.shipping_username = val
					update_params()

			phone:~
				-> params_.phone
				(val) !->
					params_.phone = val
					update_params()

			address:~
				-> params_.address
				(val) !->
					params_.address = val
					update_params()

			comment:~
				-> params_.comment
				(val) !->
					params_.comment = val
					update_params()
		}
	get_items	= ->
		items_storage.get()
	get_item	= (id) ->
		get_items()[id] || 0
	add_item	= (id) ->
		items	= get_items()
		if items[id]
			++items[id]
		else
			items[id]	= 1
		items_storage.set(items)
		items[id]
	set_item	= (id, units) !->
		items		= get_items()
		items[id]	= units
		items_storage.set(items)
	del_item	= (id) !->
		items	= get_items()
		delete items[id]
		items_storage.set(items)
	clean		= !->
		items_storage.set({})
	return {
		get_all			: get_items
		get_calculated	: (callback) !->
			items	= get_items()
			if !items
				return
			cs.api('get api/Shop/cart', {items, params.shipping_type}).then(callback)
		get				: get_item
		add				: add_item
		set				: set_item
		del				: del_item
		clean			: clean
		params			: params
	}
