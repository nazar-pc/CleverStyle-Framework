/**
 * @package   TinyMCE
 * @category  plugins
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   GNU Lesser General Public License 2.1, see license.txt
 */
Polymer(
	is				: 'cs-editor-simple'
	behaviors		: [Polymer.cs.behaviors.TinyMCE.editor]
	editor_config	: tinymce.editor_config_simple
)
