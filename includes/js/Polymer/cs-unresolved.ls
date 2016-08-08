/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
# Simplified default value declaration
registerFeatures_original	= Polymer.Base._registerFeatures
Polymer.Base._addFeature(
	_registerFeatures : !->
		@[]behaviors.push(
			ready	: !->
				t = setTimeout !~>
					if @is
						@setAttribute('cs-resolved', '')
				cs.ui.ready.then !~>
					clearTimeout(t)
					if @is
						@removeAttribute('cs-resolved')
		)
		registerFeatures_original.call(@)
)
