/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer_original	= Polymer
delay_registration	= {}
window.Polymer		= class extends Polymer
	(prototype) ->
		if delay_registration[prototype.is]
			new_prototype	= delay_registration[prototype.is]
			# Possibility to create completely alternative implementation without actually extending
			if !new_prototype.overrides
				new_prototype.behaviors = prototype.[]behaviors.slice().concat(new_prototype.behaviors || [])
				if prototype.extends
					new_prototype.extends	= prototype.extends
				delete prototype.behaviors
				delete prototype.extends
				new_prototype.behaviors.unshift(prototype)
			delete new_prototype.overrides
			prototype = new_prototype
			delete delay_registration[prototype.is]
		if prototype.is == prototype.extends
			delete prototype.extends
			delay_registration[prototype.is]	= prototype
			return
		if prototype.is == prototype.overrides
			delay_registration[prototype.is]	= prototype
			return
		Polymer_original(prototype)
	__delay_registration : {}
already_registered_modules	= {}
register_original			= Polymer.DomModule.register
# TODO: __proto__ might be used here, but shitty IE10 doesn't support it
Object.getPrototypeOf(document.createElement('dom-module')).register = !->
	if @id
		if already_registered_modules[@id]
			return
		if @getAttribute('overrides') == @id
			already_registered_modules[@id] = true
	register_original.call(@)
