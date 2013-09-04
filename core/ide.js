/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
/**
 * File is not included anywhere!
 * Next code only for IDE inspections and autocomplete
 */
var L = {},
	base_url = '',
	current_base_url = '',
	public_key = '',
	rules_text = '',
	module = '',
	in_admin = 1,
	debug = 0,
	session_id = '',
	cookie_prefix = '',
	cookie_domain = '',
	cookie_path = '',
	protocol = '',
	route = [];
cs.debug_window();
cs.admin_cache();
cs.db_test();
cs.storage_test();
cs.blocks_toggle('');
cs.json_decode();
cs.block_switch_textarea('');
cs.base64_encode();