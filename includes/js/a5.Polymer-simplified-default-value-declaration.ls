/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
# Simplified default value declaration
registerFeatures_original	= Polymer.Base._registerFeatures
normalize_properties		= (properties) !->
	if properties
		for property, value of properties
			type =
				switch typeof value
				| 'boolean'	=> Boolean
				| 'number'	=> Number
				| 'string'	=> String
				| otherwise	=>
					if value instanceof Date
						Date
					else if value instanceof Array
						Array
			if type
				properties[property] =
					type	: type,
					value	: value
Polymer.Base._addFeature(
	_registerFeatures : !->
		normalize_properties(@properties)
		if @behaviors
			@behaviors.forEach (behavior) !->
				normalize_properties(behavior.properties)
		registerFeatures_original.call(@)
)
