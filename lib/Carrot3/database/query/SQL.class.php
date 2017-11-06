<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage database.query
 */

namespace Carrot3;

/**
 * SQL生成に関するユーティリティ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class SQL {
	const TEMPORARY = 1;

	/**
	 * @access private
	 */
	private function __construct () {
	}

	/**
	 * SELECTクエリー文字列を返す
	 *
	 * @access public
	 * @param string[] $fields フィールド
	 * @param string[] $tables テーブル名の配列
	 * @param mixed $criteria 抽出条件
	 * @param mixed $order ソート順
	 * @param string $group グループ化
	 * @param integer $page ページ
	 * @param integer $pagesize ページサイズ
	 * @return string クエリー文字列
	 * @static
	 */
	static public function getSelectQuery ($fields, $tables, $criteria = null, $order = null, $group = null, $page = null, $pagesize = null) {
		$query = Tuple::create();
		$query[] = 'SELECT';
		$query[] = self::getFieldsString($fields);
		$query[] = self::getFromString($tables);
		$query[] = self::getCriteriaString($criteria);
		$query[] = self::getGroupString($group);
		$query[] = self::getOrderString($order);
		$query[] = self::getOffsetString($page, $pagesize);
		return $query->trim()->join(' ');
	}

	/**
	 * INSERTクエリー文字列を返す
	 *
	 * @access public
	 * @param mixed $table テーブル名又はテーブル
	 * @param mixed $values フィールドの値
	 * @param Database $db 対象データベース
	 * @return string クエリー文字列
	 * @static
	 */
	static public function getInsertQuery ($table, $values, Database $db = null) {
		if (!$db) {
			$db = Database::getInstance();
		}
		if ($table instanceof TableHandler) {
			$table = $table->getName();
		}
		if (is_array($values)) {
			$values = Tuple::create($values);
		} else if ($values instanceof ParameterHolder) {
			$values = Tuple::create($values->getParameters());
		}
		$values = $db->quote($values);

		return sprintf(
			'INSERT INTO %s (%s) VALUES (%s)',
			$table,
			$values->getKeys()->join(', '),
			$values->join(', ')
		);
	}

	/**
	 * UPDATEクエリー文字列を返す
	 *
	 * @access public
	 * @param mixed $table テーブル名又はテーブル
	 * @param mixed $values フィールドの値
	 * @param mixed $criteria 抽出条件
	 * @param Database $db 対象データベース
	 * @return string クエリー文字列
	 * @static
	 */
	static public function getUpdateQuery ($table, $values, $criteria, Database $db = null) {
		if (StringUtils::isBlank($criteria = self::getCriteriaString($criteria))) {
			throw new DatabaseException('抽出条件がありません。');
		}
		if (!$db) {
			$db = Database::getInstance();
		}
		if ($table instanceof TableHandler) {
			$table = $table->getName();
		}

		if (is_array($values)) {
			$values = Tuple::create($values);
		} else if ($values instanceof ParameterHolder) {
			$values = Tuple::create($values->getParameters());
		}

		$fields = Tuple::create();
		foreach ($values as $key => $value) {
			$fields[] = sprintf('%s=%s', $key, $db->quote($value));
		}

		return sprintf('UPDATE %s SET %s %s', $table, $fields->join(', '), $criteria);
	}

	/**
	 * DELETEクエリー文字列を返す
	 *
	 * @access public
	 * @param mixed $table テーブル名又はテーブル
	 * @param mixed $criteria 抽出条件
	 * @return string クエリー文字列
	 * @static
	 */
	static public function getDeleteQuery ($table, $criteria) {
		if (StringUtils::isBlank($criteria = self::getCriteriaString($criteria))) {
			throw new DatabaseException('抽出条件がありません。');
		}
		if ($table instanceof TableHandler) {
			$table = $table->getName();
		}
		return sprintf('DELETE %s %s', self::getFromString($table), $criteria);
	}

	/**
	 * CREATE TABLEクエリー文字列を返す
	 *
	 * @access public
	 * @param string $table テーブル名
	 * @param string[] $fields フィールド定義等
	 * @param integer $flags フラグのビット列
	 *   self::TEMPORARY テンポラリテーブル
	 * @static
	 */
	static public function getCreateTableQuery ($table, $fields, $flags = 0) {
		$fields = Tuple::create($fields);
		foreach ($fields as $key => $field) {
			if (is_numeric($key)) {
				$fields[$key] = $field;
			} else {
				$fields[$key] = $key . ' ' . $field;
			}
		}
		if ($flags & self::TEMPORARY) {
			return sprintf('CREATE TEMPORARY TABLE %s (%s)', $table, $fields->join(','));
		} else {
			return sprintf('CREATE TABLE %s (%s)', $table, $fields->join(','));
		}
	}

	/**
	 * DROP TABLEクエリー文字列を返す
	 *
	 * @access public
	 * @param mixed $table テーブル名又はテーブル
	 * @static
	 */
	static public function getDropTableQuery ($table) {
		if ($table instanceof TableHandler) {
			$table = $table->getName();
		}
		return sprintf('DROP TABLE %s', $table);
	}

	static private function getFieldsString ($fields = null) {
		if (!($fields instanceof FieldSet)) {
			$fields = new FieldSet($fields);
		}
		if (!$fields->count()) {
			$fields[] = '*';
		}
		return $fields->getContents();
	}

	static private function getFromString ($tables) {
		if (!($tables instanceof FieldSet)) {
			$tables = new FieldSet($tables);
		}
		return 'FROM ' . $tables->getContents();
	}

	static private function getCriteriaString ($criteria) {
		if (!($criteria instanceof Criteria)) {
			$criteria = new Criteria($criteria);
		}
		if ($criteria->count()) {
			return 'WHERE ' . $criteria->getContents();
		}
	}

	static private function getOrderString ($order) {
		if (!($order instanceof FieldSet)) {
			$order = new FieldSet($order);
		}
		if ($order->count()) {
			return 'ORDER BY ' . $order->getContents();
		}
	}

	static private function getGroupString ($group) {
		if (!($group instanceof FieldSet)) {
			$group = new FieldSet($group);
		}
		if ($group->count()) {
			return 'GROUP BY ' . $group->getContents();
		}
	}

	static private function getOffsetString ($page, $pagesize) {
		if ($page && $pagesize) {
			return sprintf('LIMIT %d OFFSET %d', $pagesize, ($page - 1) * $pagesize);
		}
	}
}
