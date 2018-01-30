/**
 * @package CleverStyle Framework
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
# JS override
delay_registration	= {}
polymerFn_original	= Polymer._polymerFn
Polymer._polymerFn	= (info) ->
	if typeof info != 'function'
		if delay_registration[info.is]
			new_prototype	= delay_registration[info.is]
			# Possibility to create completely alternative implementation without actually extending
			if !new_prototype.overrides
				new_prototype.behaviors = info.[]behaviors.slice().concat(new_prototype.behaviors || [])
				if info.extends
					new_prototype.extends	= info.extends
				delete info.behaviors
				delete info.extends
				new_prototype.behaviors.unshift(info)
			delete new_prototype.overrides
			info = new_prototype
			delete delay_registration[info.is]
		if info.is == info.extends
			delete info.extends
			delay_registration[info.is]	= info
			return
		if info.is == info.overrides
			delay_registration[info.is]	= info
			return
	polymerFn_original.call(@, info)
# DOM module override
already_registered_modules	= {}
register_original			= Polymer.DomModule.prototype.register
document.createElement('dom-module').__proto__.register = !->
	if @id
		if already_registered_modules[@id]
			return
		if @getAttribute('overrides') == @id
			already_registered_modules[@id] = true
	register_original.call(@)
