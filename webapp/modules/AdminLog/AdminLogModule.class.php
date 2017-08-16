<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage AdminLog
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

namespace Carrot3\AdminLogModule;
use \Carrot3 as C;

class AdminLogModule extends C\Module {
	private $logger;

	public function getTitle () {
		return '管理ログ閲覧モジュール';
	}

	public function getMenuTitle () {
		return '管理ログ';
	}

	/**
	 * 対象ロガーを返す
	 *
	 * @access public
	 * @return Logger ロガー
	 */
	public function getLogger () {
		if (!$this->logger) {
			$this->logger = C\LogManager::getInstance()->getPrimaryLogger();
		}
		return $this->logger;
	}

	/**
	 * 対象日付を返す
	 *
	 * @access public
	 * @return Date 日付
	 */
	public function getDate () {
		if ($this->request['date']) {
			return C\Date::create($this->request['date']);
		} else {
			return $this->getLogger()->getLastDate();
		}
	}

	/**
	 * 指定日付のログエントリーを返す
	 *
	 * @access public
	 * @return mixed ファイル又はディレクトリ
	 */
	public function getEntries (Date $date = null) {
		if (!$date) {
			$date = $this->getDate();
		}
		return $this->getLogger()->getEntries($date);
	}

	/**
	 * 日付配列を返す
	 *
	 * @access public
	 * @return mixed[][] 日付配列
	 */
	public function getDates () {
		return $this->getLogger()->getDates();
	}
}

