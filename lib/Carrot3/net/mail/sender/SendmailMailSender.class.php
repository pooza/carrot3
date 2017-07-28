<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mail.sender
 */

namespace Carrot3;

/**
 * sendmailコマンドによるメール送信機能
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class SendmailMailSender extends MailSender {

	/**
	 * 初期化
	 *
	 * @access public
	 * @return string 利用可能ならTrue
	 */
	public function initialize () {
		try {
			$this->createCommand();
			return true;
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
		$sendmail = $this->createCommand();
		$sendmail->push('-f');
		$sendmail->push($mail->getHeader('from')->getEntity()->getContents());

		if (BS_DEBUG) {
			$to = AdministratorRole::getInstance()->getMailAddress();
			$sendmail->push($to->getContents());
		} else {
			$sendmail->push('-t');
		}

		$command = new CommandLine('cat');
		$command->push($mail->getFile()->getPath());
		$command->registerPipe($sendmail);
		$command->setBackground(true);
		$command->execute();

		if ($code = $command->getReturnCode()) {
			$message = new StringFormat('sendmailが、終了コード%dを返しました。');
			$message[] = $code;
			throw new MailException($message);
		}

		$this->log($mail);
	}

	/**
	 * sendmailコマンドを返す
	 * 
	 * @access protected
	 * @return CommandLine sendmailコマンド
	 */
	protected function createCommand () {
		$command = new CommandLine('sbin/sendmail');
		$command->setDirectory(FileUtils::getDirectory('sendmail'));
		$command->push('-i');
		return $command;
	}
}

