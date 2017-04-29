/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
# Simplified default value declaration
polymerFn_original	= Polymer._polymerFn
ready				= false
Polymer._polymerFn	= (info) ->
	if typeof info != 'function'
		info.[]behaviors.push(
			attached	: !->
				if ready
					return
				@setAttribute('cs-resolved', '')
				cs.ui.ready.then !~>
					ready	:= true
					@removeAttribute('cs-resolved')
		)
	polymerFn_original.call(@, info)
