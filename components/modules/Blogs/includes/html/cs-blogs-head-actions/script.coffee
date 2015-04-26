###*
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
do (L = cs.Language) ->
	Polymer(
		publish				:
			admin			: false
			can_write_post	: false
		administration_text	: L.administration
		new_post_text		: L.new_post
		drafts_text			: L.drafts
	);
