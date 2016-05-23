/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
requirejs.config(
	baseUrl		: '/'
	paths		:
		jssha			: 'includes/js/modules/jsSHA-2.1.0'
		autosize		: 'includes/js/modules/autosize.min'
		html5sortable	: 'includes/js/modules/html5sortable.min.0.2.8'
	waitSeconds	: 60
)
# Now let's define bundled libraries so that they can be used as AMD modules
if window.$
	define('jquery', -> $)
else
	requirejs.config(
		paths	:
			jquery	: 'includes/js/jquery/jquery-3.0.0-pre'
	)
define('sprintf-js', -> {sprintf, vsprintf})
