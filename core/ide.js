/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
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
	routing = [];
debug_window();
admin_cache();
db_test();
storage_test();
blocks_toggle('');
json_decode();
block_switch_textarea('');
base64_encode();