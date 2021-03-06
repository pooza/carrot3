<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage serialize.storage.database
 */

namespace Carrot3;

/**
 * データベースシリアライズストレージ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class DatabaseSerializeStorage extends SerializeStorage {
	use BasicObject;
	const TABLE_NAME = 'serialize_entry';
	private $table;

	/**
	 * 初期化
	 *
	 * @access public
	 * @param SerializeHandler $handler
	 * @return bool
	 */
	public function initialize (SerializeHandler $handler):bool {
		parent::initialize($handler);
		try {
			$this->table = TableHandler::create(self::TABLE_NAME);
			return true;
		} catch (DatabaseException $e) {
			return false;
		}
	}

	/**
	 * テーブルを返す
	 *
	 * @access public
	 * @return TableHandler テーブル
	 */
	public function getTable () {
		return $this->table;
	}

	/**
	 * 属性を設定
	 *
	 * @access public
	 * @param string $name 属性の名前
	 * @param mixed $value 値
	 */
	public function setAttribute (string $name, $value) {
		$values = [
			'id' => $name,
			'data' => $this->getSerializer()->encode($value),
			'update_date' => Date::create()->format('Y-m-d H:i:s'),
		];

		if ($record = $this->getTable()->getRecord($name)) {
			$record->update($values);
		} else {
			$this->getTable()->createRecord($values);
		}
	}

	/**
	 * 属性を削除
	 *
	 * @access public
	 * @param string $name 属性の名前
	 */
	public function removeAttribute (string $name) {
		if ($record = $this->getTable()->getRecord($name)) {
			$record->delete();
		}
	}

	/**
	 * 属性を返す
	 *
	 * @access public
	 * @param string $name 属性の名前
	 * @param Date $date 比較する日付 - この日付より古い属性値は破棄
	 * @return mixed 属性値
	 */
	public function getAttribute (string $name, Date $date = null) {
		if (!$record = $this->getTable()->getRecord($name)) {
			return null;
		}
		if ($date && $record->getUpdateDate()->isPast($date)) {
			$record->delete();
			return null;
		}
		return $this->getSerializer()->decode($record['data']);
	}

	/**
	 * 属性の更新日を返す
	 *
	 * @access public
	 * @param string $name 属性の名前
	 * @return Date 更新日
	 */
	public function getUpdateDate (string $name):?Date {
		if (!$record = $this->getTable()->getRecord($name)) {
			return null;
		}
		return $record->getUpdateDate();
	}

	/**
	 * クリア
	 *
	 * @access public
	 */
	public function clear () {
		$this->getTable()->clear();
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return 'データベースシリアライズストレージ';
	}
}
