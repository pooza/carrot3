<?php
/**
 * @package jp.co.b-shock.carrot3
 */

namespace Carrot3;

/**
 * 例外
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class Exception extends \Exception {
	use BasicObject;

	/**
	 * @access public
	 * @param mixed $message メッセージ
	 * @param int $code コード
	 * @param Exception $prev 直前の例外。例外の連結に使用。
	 */
	public function __construct ($message = null, $code = 0, Exception $prev = null) {
		if ($message instanceof MessageContainer) {
			$message = $message->getMessage();
		}
		if (!is_numeric($code)) {
			$code = 0;
		}
		parent::__construct($message, $code, $prev);
		if ($this->isLoggable()) {
			LogManager::getInstance()->put($this);
		}
		if ($this->isAlertable()) {
			$this->alert();
		}
	}

	/**
	 * アラートをとばす
	 *
	 * @access public
	 * @return string 名前
	 */
	public function alert () {
		$json = new JSONRenderer;
		$json->setContents([
			'service' => $this->controller->getHost()->getName(),
			'class' => Utils::getClass($this),
			'message' => $this->getMessage(),
		]);
		$alerter = $this->loader->createObject(BS_ALERT_CLASS);
		if ($alerter instanceof ExceptionAlerter) {
			$alerter->alert($json);
		}
	}

	/**
	 * ログを書き込むか
	 *
	 * @access public
	 * @return bool ログを書き込むならTrue
	 */
	public function isLoggable ():bool {
		return true;
	}

	/**
	 * アラートを送るか
	 *
	 * @access public
	 * @return bool アラートを送るならTrue
	 */
	public function isAlertable ():bool {
		return false;
	}
}
