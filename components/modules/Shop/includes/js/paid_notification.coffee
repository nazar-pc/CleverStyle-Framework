###*
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
$ ->
	if cs.route[0] == 'orders_' && location.search
		L		= cs.Language
		query	= location.search.substr(1).split('&')
		query.forEach (q) ->
			q	= q.split('=')
			switch q[0]
				when 'paid_success'
					UIkit.notify(
						L.shop_paid_success_notification(q[1])
						status	: 'success'
						timeout	: 0
					)
				when 'paid_error'
					UIkit.notify(
						L.shop_paid_error_notification(q[1])
						status	: 'danger'
						timeout	: 0
					)
