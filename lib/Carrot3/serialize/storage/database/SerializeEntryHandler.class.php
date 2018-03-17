<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage serialize.storage.database
 */

namespace Carrot3;

/**
 * シリアライズテーブル
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class SerializeEntryHandler extends TableHandler {

	/**
	 * レコード追加可能か？
	 *
	 * @access protected
	 * @return bool レコード追加可能ならTrue
	 */
	protected function isInsertable () {
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
	public function createRecord ($values, $flags = Database::WITHOUT_LOGGING) {
		$db = $this->getDatabase();
		$query = SQL::getInsertQuery($this->getName(), $values, $db);
		$db->exec($query);
		return $values[$this->getKeyField()];
	}

	/**
	 * レコードの全消去が可能か？
	 *
	 * @access protected
	 * @return bool レコード追加可能ならTrue
	 */
	protected function isClearable () {
		return true;
	}

	/**
	 * テーブル名を返す
	 *
	 * @access public
	 * @return string テーブル名
	 */
	public function getName () {
		return DatabaseSerializeStorage::TABLE_NAME;
	}

	/**
	 * データベースを返す
	 *
	 * @access public
	 * @return Database データベース
	 */
	public function getDatabase () {
		return Database::getInstance('serialize');
	}

	/**
	 * スキーマを返す
	 *
	 * @access public
	 * @return Tuple フィールド情報の配列
	 */
	public function getSchema () {
		return Tuple::create([
			'id' => 'varchar(128) NOT NULL PRIMARY KEY',
			'update_date' => 'timestamp NOT NULL',
			'data' => 'TEXT',
		]);
	}
}
