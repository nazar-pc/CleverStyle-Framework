###*
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
Polymer(
	'is'		: 'cs-blogs-posts'
	'extends'	: 'section'
	properties	:
		comments_enabled	: false
	ready		: ->
		@jsonld = JSON.parse(@querySelector('script').innerHTML)
		@posts	= @jsonld['@graph']
);
