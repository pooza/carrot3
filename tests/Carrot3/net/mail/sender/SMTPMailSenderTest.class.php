<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class SMTPMailSenderTest extends Test {
	public function execute () {
		$sender = new SMTPMailSender;
		if (StringUtils::isBlank(BS_SMTP_HOST)) {
			$this->assert('initialize', !$sender->initialize());
		} else {
			$this->assert('initialize', $sender->initialize());
			$mail = new SmartyMail;
			$dir = FileUtils::getDirectory('sample');
			$mail->getRenderer()->setTemplate($dir->getEntry('Exception.mail.tpl'), 'TemplateFile');
			$mail->getRenderer()->setAttribute('message', Utils::getClass($this));
			$mail->getRenderer()->setAttribute('priority', Utils::getClass($this));
			$this->assert('send', !$sender->send($mail));
		}
	}
}
