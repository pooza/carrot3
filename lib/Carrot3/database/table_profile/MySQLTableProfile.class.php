<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage database.table_profile
 */

namespace Carrot3;

/**
 * MySQLテーブルのプロフィール
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class MySQLTableProfile extends TableProfile {
	private $storageEngine;

	/**
	 * @access public
	 * @param string $table テーブル名
	 */
	public function __construct ($table, Database $database = null) {
		if (mb_ereg('^`([_[:alnum:]]+)`$', $table, $matches)) {
			$table = $matches[1];
		}
		parent::__construct($table, $database);
	}

	/**
	 * テーブルのフィールドリストを配列で返す
	 *
	 * @access public
	 * @return Tuple フィールドのリスト
	 */
	public function getFields () {
		if (!$this->fields) {
			$query = SQL::getSelectQuery(
				'column_name,data_type,character_maximum_length,is_nullable,column_default,extra',
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
				'constraint_type=' . $this->database->quote('PRIMARY KEY') . ',name'
			);
			foreach ($this->database->query($query) as $row) {
				$criteria = $this->getCriteria();
				$criteria->register('constraint_name', $row['name']);
				$query = SQL::getSelectQuery(
					'column_name,referenced_table_name,referenced_column_name',
					'information_schema.key_column_usage',
					$criteria,
					'ordinal_position'
				);
				$row['fields'] = $this->database->query($query)->fetchAll();
				$this->constraints[$row['name']] = $row;
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
		$criteria->register('table_schema', $this->database['database_name']);
		$criteria->register('table_name', $this->getName());
		return $criteria;
	}

	/**
	 * ストレージエンジンを返す
	 *
	 * @access public
	 * @return ストレージエンジン
	 */
	public function getStorageEngine () {
		if (!$this->storageEngine) {
			$criteria = new Criteria;
			$criteria->register('table_schema', $this->getDatabase()['database_name']);
			$criteria->register('table_name', $this->getName());
			$sql = SQL::getSelectQuery(
				['engine'],
				['information_schema.tables'],
				$criteria
			);
			$result = $this->getDatabase()->query($sql)->fetchAll()[0];
			$this->storageEngine = $result['engine'];
		}
		return $this->storageEngine;
	}

	/**
	 * 最適化可能か？
	 *
	 * @access public
	 * @return boolean
	 */
	public function isOptimizable () {
		return ($this->getStorageEngine() != 'InnoDB');
	}
}

