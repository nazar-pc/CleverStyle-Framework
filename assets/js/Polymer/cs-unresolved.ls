/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
# Simplified default value declaration
registerFeatures_original	= Polymer.Base._registerFeatures
ready						= false
Polymer.Base._addFeature(
	_registerFeatures : !->
		@[]behaviors.push(
			attached	: !->
				if ready
					return
				@setAttribute('cs-resolved', '')
				cs.ui.ready.then !~>
					ready	:= true
					@removeAttribute('cs-resolved')
		)
		registerFeatures_original.call(@)
)
