<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage database.table_profile
 */

namespace Carrot3;

/**
 * SQLiteテーブルのプロフィール
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class SQLiteTableProfile extends TableProfile {

	/**
	 * テーブルのフィールドリストを配列で返す
	 *
	 * @access public
	 * @return Tuple フィールドのリスト
	 */
	public function getFields () {
		if (!$this->fields) {
			$this->fields = Tuple::create();
			$query = 'PRAGMA table_info(' . $this->getName() . ')';
			foreach ($this->getDatabase()->query($query) as $row) {
				$this->fields[$row['name']] = [
					'column_name' => $row['name'],
					'data_type' => StringUtils::toLower($row['type']),
					'is_nullable' => $row['notnull'],
					'column_default' => $row['dflt_value'],
				];
			}
		}
		return $this->fields;
	}

	/**
	 * テーブルの制約リストを配列で返す
	 *
	 * @access public
	 * @return Tuple 制約のリスト
	 */
	public function getConstraints () {
		if (!$this->constraints) {
			$this->constraints = Tuple::create();
			$query = 'PRAGMA index_list(' . $this->getName() . ')';
			foreach ($this->getDatabase()->query($query) as $rowKey) {
				$key = [
					'name' => $rowKey['name'],
					'fields' => [],
				];
				$query = 'PRAGMA index_info(' . $rowKey['name'] . ')';
				foreach ($this->getDatabase()->query($query) as $rowField) {
					$key['fields'][] = ['column_name' => $rowField['name']];
				}
				$this->constraints[$rowKey['name']] = $key;
			}
		}
		return $this->constraints;
	}
}
