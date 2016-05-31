/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	is	: 'cs-shop-order-paid-notification'
	created : !->
		if !location.search
			return
		L		= cs.Language('shop_')
		query	= location.search.substr(1).split('&')
		query.forEach (q) !->
			q	= q.split('=')
			switch q[0]
				when 'paid_success'
					cs.ui.notify(
						L.paid_success_notification(q[1])
						'success'
					)
				when 'paid_error'
					cs.ui.notify(
						L.paid_error_notification(q[1])
						'error'
					)
)
