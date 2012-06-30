<?php
namespace cs;
class Text {
	/**
	 * Gets text
	 *
	 * @param int        $database
	 * @param string     $group
	 * @param string     $label
	 * @param int        $id
	 *
	 * @return bool|string
	 */
	function get ($database, $group, $label, $id = 0) {
		global $Cache, $L, $db;
		$index = 'texts/'.$database.'/'.md5($group).md5($label).'_'.$L->lang;
		if ($id != 0) {
			if (($data = $Cache->{'texts/'.$database.'/'.$id}) === false) {
				$data = $db->$database->qf('SELECT `group`, `label` FROM `[prefix]texts` WHERE `id` = '.(int)$id);
				if (is_array($data)) {
					$Cache->{'texts/'.$database.'/'.$id} = _json_encode($data);
				}
			}
			if (is_array($data)) {
				list($group, $label) = $data;
			}
			unset($data);
		}
		if (($text = $Cache->{$index}) === false) {
			$text = $db->$database->qf('
				SELECT `text`
				FROM `[prefix]texts`
				WHERE
					`group`	= '.$database->s($group).' AND
					`label`	= '.$database->s($label).' AND
					`lang`	= '.$database->s($L->clang).'
				LIMIT 1',
				'text'
			);
			if (!is_array($text) || empty($text)) {
				$text = $db->$database->qf('
						SELECT `text`, `lang`
						FROM `[prefix]texts`
						WHERE
							`group`	= '.$database->s($group).' AND
							`label`	= '.$database->s($label).'
						LIMIT 1'
				);
				$lang	= '';
				if (is_array($text)) {
					list($text, $lang) = $text;
				}
				global $Config;
				if ($Config->core['auto_translation'] && $lang && $text){
					$engine_class	= '\\cs\\translate\\'.$Config->core['auto_translation_engine']['name'];
					$text			= $engine_class::translate($text, $lang, $L->lang) ?: $text;
					$this->set($database, $group, $label, $text);
				}
			}
			$Cache->{'texts/'.$database.'/'.$index} = $text;
		}
		return $text;
	}
	/**
	 * Sets text
	 *
	 * @param int          $database
	 * @param string       $group
	 * @param string       $label
	 * @param string       $text
	 *
	 * @return bool|string
	 */
	function set ($database, $group, $label, $text) {
		global $Cache, $L, $db;
		$index = 'texts/'.$database.'/'.md5($group).md5($label).'_'.$L->lang;
		unset($Cache->{$index});
		$database = $db->$database();
		/**
		 * @var \cs\database\_Abstract $database
		 */
		if ($id = $database->q('
			SELECT `id`
			FROM `[prefix]texts`
			WHERE
				`group`	= '.$database->s($group).' AND
				`label`	= '.$database->s($label).' AND
				`lang`	= '.$database->s($L->clang).'
			LIMIT 1',
			'id'
		)) {
			if ($database->q('UPDATE `[prefix]texts` SET `text` = '.$database->s($text).' WHERE `id` = '.$id.' LIMIT 1')) {
				return '{¶'.$id.'}';
			} else {
				return false;
			}
		} else {
			$database->q('INSERT INTO `[prefix]texts` (`text`, `lang`) VALUES '.'('.$database->s($text).', '.$database->s($L->clang).')');
			$id = $database->id();
			global $Config;
			if ($id && $id % $Config->core['inserts_limit'] == 0) { //Clean up old texts
				$database->q("DELETE FROM `[prefix]texts` WHERE `lang` = ''");
			}
			if ($id) {
				return '{¶'.$id.'}';
			} else {
				return false;
			}
		}
	}
	/**
	 * @param int        $database
	 * @param string     $group
	 * @param string     $label
	 *
	 * @return bool
	 */
	function del ($database, $group, $label) {
		global $Cache, $L, $db;
		$index = 'texts/'.$database.'/'.md5($group).md5($label).'_'.$L->lang;
		unset($Cache->{$index});
		$ids = $db->$database()->q('
			SELECT `id`
			FROM `[prefix]texts`
			WHERE
				`group`	= '.$db->$database()->s($group).' AND
				`label`	= '.$db->$database()->s($label),
			'id'
		);
		foreach ($ids as $id) {
			unset($Cache->{'texts/'.$database.'/'.$id});
		}
		unset($ids, $id);
		return $db->$database()->q('
			UPDATE `[prefix]texts`
			SET
				`label`	= null,
				`group`	= null,
				`text`	= null,
				`lang`	= null
			WHERE
				`group`	= '.$db->$database()->s($group).' AND
				`label`	= '.$db->$database()->s($label).' AND
				`lang`	= '.$db->$database()->s($L->clang)
		);
	}
	function process ($database, $data) {
		if (!is_object($database) || empty($data)) {
			return false;
		}
		return preg_replace_callback(
			'/\{¶([0-9]*?)\}/',
			function ($input) use ($database) {
				return $this->get($database, null, null, $input[1]);
			},
			$data
		);
	}
	/**
	 * Cloning restriction
	 */
	function __clone () {}
}