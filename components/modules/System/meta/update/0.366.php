<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
$Config	= Config::instance();
$Config->core['sign_in_attempts_block_count']	= $Config->core['login_attempts_block_count'];
unset($Config->core['login_attempts_block_count']);
$Config->core['sign_in_attempts_block_time']	= $Config->core['login_attempts_block_time'];
unset($Config->core['login_attempts_block_time']);
$Config->core['auto_sign_in_after_registration']	= $Config->core['autologin_after_registration'];
unset($Config->core['autologin_after_registration']);
$Config->save();