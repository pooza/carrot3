<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage database.table_profile
 */

namespace Carrot3;

/**
 * PostgreSQLテーブルのプロフィール
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class PostgreSQLTableProfile extends TableProfile {

	/**
	 * テーブルのフィールドリストを配列で返す
	 *
	 * @access public
	 * @return Tuple フィールドのリスト
	 */
	public function getFields () {
		if (!$this->fields) {
			$query = SQL::getSelectQuery(
				'column_name,data_type,character_maximum_length,is_nullable,column_default',
				'information_schema.columns',
				$this->getCriteria(),
				'ordinal_position'
			);
			$this->fields = Tuple::create();
			foreach ($this->database->query($query) as $row) {
				$this->fields[$row['column_name']] = $row;
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
			$query = SQL::getSelectQuery(
				'constraint_name AS name,constraint_type AS type',
				'information_schema.table_constraints',
				$this->getCriteria(),
				'constraint_type=' . $this->database->quote('PRIMARY KEY') . ',constraint_name'
			);
			foreach ($this->database->query($query) as $row) {
				$criteria = $this->getCriteria();
				$criteria->register('constraint_name', $row['name']);
				$query = SQL::getSelectQuery(
					'column_name',
					'information_schema.key_column_usage',
					$criteria,
					'ordinal_position'
				);
				if ($row['fields'] = $this->database->query($query)->fetchAll()) {
					$this->constraints[$row['name']] = $row;
				}
			}
		}
		return $this->constraints;
	}

	/**
	 * 抽出条件を返す
	 *
	 * @access protected
	 * @return Criteria 抽出条件
	 */
	protected function getCriteria () {
		$criteria = $this->database->createCriteria();
		$criteria->register('table_name', $this->getName());
		return $criteria;
	}
}

