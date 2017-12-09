<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class SendmailMailSenderTest extends Test {
	public function execute () {
		$this->assert('__construct', $sender = new SendmailMailSender);
		$this->assert('initialize', $sender->initialize());

		$mail = new SmartyMail;
		$dir = FileUtils::getDirectory('sample');
		$mail->getRenderer()->setTemplate($dir->getEntry('Exception.mail.tpl'), 'TemplateFile');
		$mail->getRenderer()->setAttribute('message', Utils::getClass($this));
		$mail->getRenderer()->setAttribute('priority', Utils::getClass($this));

		$this->assert('send', !$sender->send($mail));
	}
}
