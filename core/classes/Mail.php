<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	h,
	PHPMailer;

/**
 * @method static Mail instance($check = false)
 */
class Mail extends PHPMailer {
	use Singleton;
	/**
	 * Setting base mail sending parameters according to system configuration
	 */
	function construct () {
		$Config = Config::instance();
		if ($Config->core['smtp']) {
			$this->IsSMTP();
			$this->Host       = $Config->core['smtp_host'];
			$this->Port       = $Config->core['smtp_port'] ?: $Config->core['smtp_secure'] ? 465 : 25;
			$this->SMTPSecure = $Config->core['smtp_secure'];
			if ($Config->core['smtp_auth']) {
				$this->SMTPAuth = true;
				$this->Username = $Config->core['smtp_user'];
				$this->Password = $Config->core['smtp_password'];
			}
		}
		$this->From     = $Config->core['mail_from'];
		$this->FromName = get_core_ml_text('mail_from_name');
		$this->CharSet  = 'utf-8';
		$this->IsHTML();
	}
	/**
	 * @param string $body
	 * @param string $signature
	 *
	 * @return string
	 */
	protected function body_normalization ($body, $signature) {
		if (strpos($body, '<!doctype') === 0 && strpos($body, '<body') !== false) {
			$body = "<!doctype html>\n$body";
		}
		if (strpos($body, '<html') === false) {
			if (substr($body, 0, 5) != '<body') {
				$body = h::body($body.$signature);
			} else {
				$body = str_replace('</body>', "$signature</body>", $body);
			}
			$body = h::html(
				h::{'head meta'}(
					[
						'content'    => 'text/html; charset=utf-8',
						'http-equiv' => 'Content-Type'
					]
				).
				$body
			);
		} else {
			$body = str_replace('</body>', "$signature</body>", $body);
		}
		return $body;
	}
	/**
	 * Sending of email
	 *
	 * @param array|string|string[]      $email       if emails without names - string (may be several emails separated by comma) or
	 *                                                1-dimensional array(<i>email</i>)<br>
	 *                                                else - 2-dimensional array(<i>email</i>, <i>name</i>) must be given
	 * @param string                     $subject     Mail subject
	 * @param string                     $body        html body
	 * @param string|null                $body_text   plain text body
	 * @param array|null|string          $attachments 1- or 2-dimensional array of array(<i>path</i>, <i>name</i>) or simply string with path to the file in
	 *                                                file system
	 * @param array|null|string|string[] $reply_to    Similar to <b>$email</b>
	 * @param bool|string                $signature   <b>true</b> - add system signature<br>
	 *                                                <b>false</b> - without signature<br>
	 *                                                <b>string</b> - custom signature
	 *
	 * @return bool
	 */
	function send_to ($email, $subject, $body, $body_text = null, $attachments = null, $reply_to = null, $signature = true) {
		if (!$email || !$subject || !$body) {
			return false;
		}
		foreach ($this->normalize_email($email) as $e) {
			$this->addAddress($e[0], $e[1]);
		}
		unset($e);
		$this->Subject = $subject;
		if ($signature === true) {
			if ($signature = get_core_ml_text('mail_signature')) {
				$signature = "$this->LE-- $this->LE.$signature";
			}
		} elseif ($signature) {
			$signature = "$this->LE-- $this->LE".xap($signature, true);
		} else {
			$signature = '';
		}
		$this->Body = $this->body_normalization($body, $signature);
		if ($body_text) {
			$this->AltBody = $body_text.strip_tags($signature);
		}
		if (is_array($attachments)) {
			if (count($attachments) == 2) {
				$this->AddStringAttachment($attachments[0], $attachments[1]);
			} else {
				foreach ($attachments as $a) {
					if (is_array($a)) {
						$this->AddStringAttachment($a[0], $a[1]);
					} else {
						$this->AddStringAttachment($a, pathinfo($a, PATHINFO_FILENAME));
					}
				}
			}
		} elseif (is_string($attachments)) {
			$this->AddStringAttachment($attachments, pathinfo($attachments, PATHINFO_FILENAME));
		}
		foreach ($this->normalize_email($reply_to) as $r) {
			$this->AddReplyTo($r[0], $r[1]);
		}
		unset($r);
		$result = $this->Send();
		$this->ClearAddresses();
		$this->ClearAttachments();
		$this->ClearReplyTos();
		return $result;
	}
	/**
	 * @param string $email
	 *
	 * @return string[][]
	 */
	protected function normalize_email ($email) {
		if (!$email) {
			return [];
		}
		if (is_array($email)) {
			if (count($email) == 2) {
				return [$email];
			}
			$emails = [];
			foreach ($email as $m) {
				$emails[] = is_array($m) ? $m : [$m, ''];
			}
			return $emails;
		}
		$email = _trim(explode(',', $email));
		foreach ($email as &$e) {
			$e = [$e, ''];
		}
		return $email;
	}
}
