<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage database.dsn
 */

namespace Carrot3;

/**
 * PostgreSQL用データソース名
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class PgSQLDataSourceName extends DataSourceName {

	/**
	 * @access public
	 * @param mixed[] $params 要素の配列
	 */
	public function __construct ($contents, $name = 'default') {
		parent::__construct($contents, $name);

		mb_ereg('^pgsql:(.+)$', $contents, $matches);
		foreach (mb_split(' +', $matches[1]) as $config) {
			$config = StringUtils::explode('=', $config);
			switch ($config[0]) {
				case 'host':
					$this['host'] = new Host($config[1]);
					break;
				case 'dbname':
					$this['database_name'] = $config[1];
					break;
				case 'user':
					$this['uid'] = $config[1];
					break;
			}
		}
	}

	/**
	 * データベースに接続して返す
	 *
	 * @access public
	 * @return Database データベース
	 */
	public function connect () {
		$db = new PostgreSQLDatabase($this->getContents());
		$this['version'] = $db->getVersion();
		$db->setDSN($this);
		return $db;
	}

	/**
	 * DBMS名を返す
	 *
	 * @access public
	 * @return string DBMS名
	 */
	public function getDBMS () {
		return 'PostgreSQL';
	}
}

