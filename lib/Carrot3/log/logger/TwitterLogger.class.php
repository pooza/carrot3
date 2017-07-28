<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage log.logger
 */

namespace Carrot3;

/**
 * Twitter DM送信ロガー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class TwitterLogger extends Logger {
	private $account;
	private $patterns;

	/**
	 * 初期化
	 *
	 * @access public
	 * @return string 利用可能ならTrue
	 */
	public function initialize () {
		try {
			$this->account = AuthorRole::getInstance()->getTwitterAccount();
		} catch (\Exception $e) {
			return false;
		}
		return true;
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
		if ($exception && $exception->isTweetable()) {
			$format = new StringFormat('[%s] [%s] [%s] %s');
			$format[] = $this->getServerHostName();
			$format[] = $priority;
			$format[] = $this->getClientHostName();
			$format[] = $message;
			$this->account->sendDM(
				$format->getContents(),
				AdministratorRole::getInstance()->getTwitterAccount()
			);
		}
	}
}

