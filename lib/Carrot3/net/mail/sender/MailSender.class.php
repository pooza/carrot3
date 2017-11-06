<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mail.sender
 */

namespace Carrot3;

/**
 * メール送信機能
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class MailSender {

	/**
	 * 初期化
	 *
	 * @access public
	 * @return string 利用可能ならTrue
	 */
	abstract public function initialize ();

	/**
	 * 送信
	 *
	 * @access public
	 * @param Mail $mail メール
	 */
	abstract public function send (Mail $mail);

	/**
	 * 送信ログを出力する
	 *
	 * @access protected
	 * @param Mail $mail 対象メール
	 */
	protected function log (Mail $mail) {
		$recipients = Tuple::create();
		foreach ($mail->getRecipients() as $email) {
			$recipients[] = $email->getContents();
		}

		$message = new StringFormat('%sから%s宛に、メールを送信しました。');
		$message[] = $mail->getHeader('From')->getEntity()->getContents();
		$message[] = $recipients->join(',');

		LogManager::getInstance()->put($message, $this);
	}
}
