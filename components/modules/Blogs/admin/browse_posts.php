<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012 by Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */

namespace	cs\modules\Blogs;
use			\h;
global $Index, $L, $Page;
$Index->buttons	= false;
$Page->title($L->browse_posts);
$Index->content(
	h::{'table.cs-center-all.cs-fullwidth-table'}(
		h::{'tr th.ui-widget-header.ui-corner-all'}(
			[
				$L->post_title,
				[
					'style'	=> 'width: 35%'
				]
			],
			[
				$L->post_sections,
				[
					'style'	=> 'width: 30%'
				]
			],
			[
				$L->post_tags,
				[
					'style'	=> 'width: 25%'
				]
			],
			$L->action
		).
		h::{'tr| td.ui-widget-content.ui-corner-all'}(
			get_posts_rows(isset($_POST['page']) ? $_POST['page'] : 1)
		)
	)
);