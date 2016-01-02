###*
 * @package   Blockchain payment
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
L = cs.Language
Polymer(
	properties		:
		description		: ''
		address			: ''
		amount			: Number
		progress_text	:
			type	: string
			value	: L.blockchain_payment_waiting_for_payment
	ready			: ->
		$ =>
			@set('description', JSON.parse(@description))
			@set('text', L.blockchain_payment_scan_or_transfer(@amount, @address))
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
					@set('progress_text', L.blockchain_payment_waiting_for_confirmations)
				setTimeout (=>
					@update_status()
				), 5000
		)
);
