<?php
namespace cs\translate;
abstract class _Abstract {
	/**
	 * @static
	 * Text translation from one language to another
	 *
	 * @param string $text Text for translation
	 * @param string $from Language translate from
	 * @param string $to   Language translate to
	 *
	 * @return bool|string Translated string of <b>false</b> if failed
	 */
	abstract static function translate ($text, $from, $to);
	/**
	 * Cloning restriction
	 *
	 * @final
	 */
	final function __clone () {}
}