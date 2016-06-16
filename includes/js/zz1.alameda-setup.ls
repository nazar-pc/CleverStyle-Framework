/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
requirejs.config(
	baseUrl		: '/'
	paths		:
		jssha						: 'includes/js/modules/jsSHA-2.1.0'
		autosize					: 'includes/js/modules/autosize.min'
		html5sortable				: 'includes/js/modules/html5sortable.min.0.2.8'
		'html5sortable-no-jquery'	: 'includes/js/modules/html5sortable-no-jquery'
	waitSeconds	: 60
)
# Now let's define bundled libraries so that they can be used as AMD modules
# TODO: In future we'll load jQuery as AMD module only and this thing will not be needed
if window.$
	define('jquery', -> $)
else
	requirejs.config(
		paths	:
			jquery	: cs.optimized_includes[0].shift()
	)
define('sprintf-js', -> {sprintf, vsprintf})
