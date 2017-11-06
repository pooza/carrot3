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

	/**
	 * @access public
	 * @param string $message メッセージ
	 * @param integer $code コード
	 * @param Exception $prev 直前の例外。例外の連結に使用。
	 */
	public function __construct ($message = null, $code = 0, Exception $prev = null) {
		if ($message instanceof StringFormat) {
			$message = $message->getContents();
		}
		if (!is_numeric($code)) {
			$code = 0;
		}
		parent::__construct($message, $code, $prev);
		if ($this->isLoggable()) {
			LogManager::getInstance()->put($this);
		}
	}

	/**
	 * 名前を返す
	 *
	 * @access public
	 * @return string 名前
	 */
	public function getName () {
		return Utils::getClass($this);
	}

	/**
	 * ログを書き込むか
	 *
	 * @access public
	 * @return boolean ログを書き込むならTrue
	 */
	public function isLoggable () {
		return true;
	}

	/**
	 * メールを送るか
	 *
	 * @access public
	 * @return boolean メールを送るならTrue
	 */
	public function isMailable () {
		return false;
	}

	/**
	 * ツイートするか
	 *
	 * @access public
	 * @return boolean ツイートするならTrue
	 */
	public function isTweetable () {
		return false;
	}
}
