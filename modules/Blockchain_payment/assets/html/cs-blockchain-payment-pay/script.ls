/**
 * @package  Blockchain payment
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
Polymer(
	is			: 'cs-blockchain-payment-pay'
	properties	:
		description		: ''
		address			: ''
		amount			: Number
		progress_text	: String
	ready : !->
		cs.Language('blockchain_payment_').ready().then (L) !~>
			@progress_text	= L.waiting_for_payment
			@text			= L.scan_or_transfer(@amount, @address)
		@description	JSON.parse(@description)
		new QRCode(
			@$.qr
			height	: 512
			text	: 'bitcoin:' + @address + '?amount=' + @amount
			width	: 512
		)
		@update_status()
	update_status : !->
		cs.api('get api/Blockchain_payment/' + @.dataset.id).then (data) !~>
			if parseInt(data.confirmed)
				location.reload()
				return
			if parseInt(data.paid)
				cs.Language('blockchain_payment_').ready().then (L) !~>
					@progress_text	= L.waiting_for_confirmations
			setTimeout(@~update_status, 5000)
);
