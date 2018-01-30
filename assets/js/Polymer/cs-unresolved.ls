/**
 * @package CleverStyle Framework
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
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
