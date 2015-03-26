###*
 * @package       Shop
 * @order_status  modules
 * @author        Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright     Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license       MIT License, see license.txt
###
do (L = cs.Language) ->
	Polymer(
		publish			:
			description	: ''
			address		: ''
			amount		: 0
		progress_text	: L.blockchain_payment_waiting_for_payment
		ready			: ->
			$ =>
				@description	= JSON.parse(@description)
				@text			= L.blockchain_payment_scan_or_transfer(@amount, @address)
				$(@$.qr).qrcode(
					height	: 512
					text	: 'bitcoin:' + @address + '?amount=' + @amount
					width	: 512
				)
				@update_status()
		update_status	: ->
			$.ajax(
				url		: 'api/Blockchain_payment/' + $(@).data('id')
				type	: 'get'
				success	: (data) =>
					if parseInt(data.confirmed)
						location.reload()
						return
					if parseInt(data.paid)
						@progress_text	= L.blockchain_payment_waiting_for_confirmations
					setTimeout (=>
						@update_status()
					), 5000
			)
	);
