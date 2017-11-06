<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mail.sender
 */

namespace Carrot3;

/**
 * SMTPによるメール送信機能
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class SMTPMailSender extends MailSender {
	static private $smtp;

	/**
	 * 初期化
	 *
	 * @access public
	 * @return string 利用可能ならTrue
	 */
	public function initialize () {
		try {
			return !!self::getServer();
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * 送信
	 *
	 * @access public
	 * @param Mail $mail メール
	 */
	public function send (Mail $mail) {
		$smtp = self::getServer();
		$smtp->setMail($mail);
		$response = $smtp->send();
		$this->log($mail, $response);
	}

	/**
	 * 送信ログを出力する
	 *
	 * @access protected
	 * @param Mail $mail 対象メール
	 * @param string $response レスポンス行
	 */
	protected function log (Mail $mail, $response = null) {
		$recipients = Tuple::create();
		foreach ($mail->getRecipients() as $email) {
			$recipients[] = $email->getContents();
		}

		$message = new StringFormat('%sから%s宛に、メールを送信しました。(%s)');
		$message[] = $mail->getHeader('From')->getEntity()->getContents();
		$message[] = $recipients->join(',');
		$message[] = rtrim($response);

		LogManager::getInstance()->put($message, $this);
	}

	/**
	 * SMTPサーバを返す
	 * 
	 * @access public
	 * @return SMTP SMTPサーバ
	 * @static
	 */
	static public function getServer () {
		if (!self::$smtp) {
			self::$smtp = new SMTP;
		}
		return self::$smtp;
	}
}
