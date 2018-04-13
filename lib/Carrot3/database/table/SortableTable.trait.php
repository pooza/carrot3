<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage database.table
 */

namespace Carrot3;

/**
 * ソート可能なテーブル
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
trait SortableTable {

	/**
	 * @access public
	 * @param mixed $criteria 抽出条件
	 * @param mixed $order ソート順
	 */
	public function __construct ($criteria = null, $order = null) {
		if (!$order) {
			$order = new FieldSet;
			$order[] = $this->getRankField();
			$order[] = $this->getKeyField();
		}
		parent::__construct($criteria, $order);
	}

	/**
	 * レコード追加可能か？
	 *
	 * @access protected
	 * @return bool レコード追加可能ならTrue
	 */
	protected function isInsertable ():bool {
		return true;
	}

	/**
	 * レコード作成
	 *
	 * @access public
	 * @param mixed $values 値
	 * @param int $flags フラグのビット列
	 *   Database::WITHOUT_LOGGING ログを残さない
	 * @return string レコードの主キー
	 */
	public function createRecord ($values, int $flags = 0) {
		$values[$this->getRankField()] = $this->getNextRank();
		return parent::createRecord($values, $flags);
	}

	/**
	 * 次の順位を返す
	 *
	 * @access public
	 * @return int 順位
	 */
	public function getNextRank () {
		$sql = SQL::getSelectQuery(
			'max(' . $this->getRankField() . ') as last_rank',
			$this->getName(),
			$this->getCriteria()
		);
		return $this->getDatabase()->query($sql)->fetch()['last_rank'] + 1;
	}

	/**
	 * 順位フィールド名
	 *
	 * @access public
	 * @return string 順位フィールド名
	 */
	public function getRankField () {
		return 'rank';
	}

	/**
	 * 順位をクリア
	 *
	 * @access public
	 */
	public function clearRanks () {
		if (!$criteria = $this->getCriteria()) {
			$criteria = $this->getKeyField() . ' IS NOT NULL';
		}

		$sql = SQL::getUpdateQuery(
			$this->getName(),
			[$this->getRankField() => 0],
			$criteria
		);
		$this->getDatabase()->exec($sql);
	}
}
