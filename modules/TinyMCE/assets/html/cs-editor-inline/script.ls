/**
 * @package   TinyMCE
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license   GNU Lesser General Public License 2.1, see license.txt
 */
Polymer(
	is					: 'cs-editor-inline'
	behaviors			: [
		TinyMCE_Polymer_editor_behavior,
		cs.Polymer.behaviors.inject_light_styles
	]
	_styles_dom_module	: 'cs-editor-styles'
	editor_config		: 'editor_config_inline'
)
