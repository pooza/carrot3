<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage database.table
 */

namespace Carrot3;

/**
 * テーブルイテレータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class TableIterator extends Iterator {
	private $table;

	/**
	 * @access public
	 * @param TableHandler $table テーブル
	 */
	public function __construct (TableHandler $table) {
		$this->table = $table;
		foreach ($table->getResult() as $row) {
			$this->keys[] = $row[$table->getKeyField()];
		}
	}

	/**
	 * 現在のレコードを返す
	 *
	 * @access public
	 * @return Record レコード
	 */
	public function current () {
		return $this->table->getRecord(parent::key());
	}

	/**
	 * カーソルを終端に進める
	 *
	 * @access public
	 * @return mixed 最後のエントリー
	 */
	public function forward () {
		$this->cursor = $this->table->count() - 1;
		return $this->current();
	}
}

