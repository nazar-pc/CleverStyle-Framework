###*
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
L = cs.Language
Polymer(
	publish		:
		comments_enabled	: false
	ready		: ->
		@jsonld = JSON.parse(@querySelector('script').innerHTML)
		@posts	= @jsonld['@graph']
);
