<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage log.logger
 */

namespace Carrot3;

/**
 * メール送信ロガー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class MailLogger extends Logger {
	private $patterns;

	/**
	 * 初期化
	 *
	 * @access public
	 * @return string 利用可能ならTrue
	 */
	public function initialize () {
		return !!Mail::createSender();
	}

	/**
	 * ログを出力
	 *
	 * @access public
	 * @param string $message ログメッセージ
	 * @param string $priority 優先順位
	 */
	public function put ($message, $priority) {
		try {
			$exception = null;
			$class = $this->loader->getClass($priority);
			if ($class instanceof Exception) {
				$exception = new $class;
			}
		} catch (LoaderException $e) {
			return;
		}
		if ($exception && $exception->isMailable()) {
			$this->send($message, $priority);
		}
	}

	private function send ($message, $priority) {
		$mail = new SmartyMail;
		$mail->getRenderer()->setTemplate('Exception.mail');
		$mail->getRenderer()->setAttribute(
			'from',
			RootRole::getInstance()->getMailAddress()->format()
		);
		$mail->getRenderer()->setAttribute('message', $message);
		$mail->getRenderer()->setAttribute('priority', $priority);
		$mail->send();
	}
}
