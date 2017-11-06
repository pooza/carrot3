<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage database.dsn
 */

namespace Carrot3;

/**
 * MySQL用データソース名
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class MySQLDataSourceName extends DataSourceName {

	/**
	 * @access public
	 * @param mixed[] $params 要素の配列
	 */
	public function __construct ($contents, $name = 'default') {
		parent::__construct($contents, $name);
		mb_ereg('^mysql:host=([^;]+);dbname=([^;]+);charset=([^;]+)$', $contents, $matches);
		$this['host'] = new Host($matches[1]);
		$this['database_name'] = $matches[2];
		$this['charset'] = $matches[3];
	}

	/**
	 * データベースに接続して返す
	 *
	 * @access public
	 * @return Database データベース
	 */
	public function connect () {
		try {
			$db = new MySQLDatabase(
				$this->getContents(),
				$this['uid'],
				$this->decryptPassword()
			);
			$this['version'] = $db->getVersion();
			$db->setDSN($this);
		} catch (\Exception $e) {
			$message = new StringFormat('データベース "%s" に接続できません。');
			$message[] = $this->getName();
			throw new DatabaseException($message);
		}
		return $db;
	}

	/**
	 * DBMS名を返す
	 *
	 * @access public
	 * @return string DBMS名
	 */
	public function getDBMS () {
		return 'MySQL';
	}
}
