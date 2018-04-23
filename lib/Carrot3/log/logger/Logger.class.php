<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage log.logger
 */

namespace Carrot3;

/**
 * 抽象ロガー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class Logger {
	use BasicObject;
	const DEFAULT_PRIORITY = 'Info';

	/**
	 * 初期化
	 *
	 * @access public
	 * @return bool
	 * @abstract
	 */
	abstract public function initialize ():bool;

	/**
	 * ログを出力
	 *
	 * @access public
	 * @param string $message ログメッセージ
	 * @param string $priority 優先順位
	 * @abstract
	 */
	abstract public function put ($message, $priority);

	/**
	 * サーバアントホスト名を返す
	 *
	 * $this->controller->getHost()->getName() が利用できない状況がある
	 *
	 * @access protected
	 * @return string サーバホスト名
	 */
	protected function getServerHostName () {
		return $_SERVER['SERVER_NAME'];
	}

	/**
	 * クライアントホスト名を返す
	 *
	 * $this->request->getHost()->getName() が利用できない状況がある
	 *
	 * @access protected
	 * @return string クライアントホスト名
	 */
	protected function getClientHostName () {
		foreach (['HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
			if (isset($_SERVER[$key]) && ($value = $_SERVER[$key])) {
				try {
					return trim(mb_split('[:,]', $value)[0]);
				} catch (\Exception $e) {
					return $value;
				}
			}
		}
	}

	/**
	 * 直近日を返す
	 *
	 * @access public
	 * @return Date 直近日
	 */
	public function getLastDate () {
		if ($first = $this->getDates()->getIterator()->getFirst()) {
			return Date::create($first);
		}
		return Date::create();
	}

	/**
	 * 日付の配列を返す
	 *
	 * @access public
	 * @return Tuple 日付の配列
	 */
	public function getDates () {
		throw new LogException(Utils::getClass($this) . 'はgetDatesに対応していません。');
	}

	/**
	 * エントリーを抽出して返す
	 *
	 * @access public
	 * @param string Date 対象日付
	 * @return Tuple エントリーの配列
	 */
	public function getEntries (Date $date) {
		throw new LogException(Utils::getClass($this) . 'はgetEntriesに対応していません。');
	}

	/**
	 * 例外か？
	 *
	 * @access protected
	 * @param string $priority 優先順位
	 * @return bool 例外ならTrue
	 */
	protected function isException ($priority):bool {
		return mb_ereg('Exception$', $priority);
	}
}
