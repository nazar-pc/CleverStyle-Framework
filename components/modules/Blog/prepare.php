<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Blog;
use			\h;
global $Core, $Index, $Config, $Blog, $Page;
include_once MFOLDER.'/class.php';
$Core->create('cs\\modules\\Blog\\Blog');
$Index->title_auto	= false;
$Index->init_auto	= false;
$rc					= $Config->routing['current'];
if (isset($rc[0]) && mb_strpos($rc[0], ':')) {
	$post	= (int)mb_substr($rc[0], mb_strrpos($rc[0], ':')+1);
	if (!$post) {
		define('ERROR_PAGE', 404);
		return;
	}
	$post	= $Blog->get($post);
	if (!$post) {
		define('ERROR_PAGE', 404);
		return;
	}
	$Page->title($post['title']);
	$Index->content(
		h::{'section.cs-blog-post article'}(
			h::header(
				h::h2($post['title']).
				h::{'p a'}(
					array_map(
						function ($section) use ($Blog) {
							$section	= $Blog->get_section($section);
							return [
								$section['title'],
								[
									'href'	=> $section['full_path']
								]
							];
						},
						$post['sections']
					)
				)
			).
			h::article($post['content'])//TODO show tags, author and date. Tags will be links to searching by tag name
		)
	);
}