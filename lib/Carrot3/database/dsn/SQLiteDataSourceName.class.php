<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage database.dsn
 */

namespace Carrot3;

/**
 * SQLite用データソース名
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class SQLiteDataSourceName extends DataSourceName {

	/**
	 * @param string $contents DSN値
	 * @param string $name DSN名
	 */
	public function __construct (string $contents, string $name = 'default') {
		parent::__construct($contents, $name);
		mb_ereg('^sqlite:(.+)$', $contents, $matches);
		$this['file'] = new File($matches[1]);
	}

	/**
	 * データベースに接続して返す
	 *
	 * @access public
	 * @return Database データベース
	 */
	public function connect () {
		$db = new SQLiteDatabase($this->getContents());
		$db->setDSN($this);
		$this['version'] = $db->getVersion();
		return $db;
	}

	/**
	 * DBMS名を返す
	 *
	 * @access public
	 * @return string DBMS名
	 */
	public function getDBMS () {
		return 'SQLite';
	}
}
