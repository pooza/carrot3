<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage database.database
 */

namespace Carrot3;

/**
 * MySQLデータベース
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class MySQLDatabase extends Database {

	/**
	 * テーブル名のリストを配列で返す
	 *
	 * @access public
	 * @return Tuple テーブル名のリスト
	 */
	public function getTableNames () {
		if (!$this->tables) {
			$this->tables = Tuple::create();
			foreach ($this->query('SHOW TABLES')->fetchAll(\PDO::FETCH_NUM) as $row) {
				$this->tables[] = $row[0];
			}
		}
		return $this->tables;
	}

	/**
	 * インデックスを作成
	 *
	 * @access public
	 * @param string $table 対象テーブル
	 * @param Tuple $fields インデックスを構成するフィールドの配列
	 */
	public function createIndex ($table, Tuple $fields) {
		$query = new StringFormat('ALTER TABLE %s ADD KEY (%s)');
		$query[] = $table;
		$query[] = $fields->join(',');
		$this->exec($query->getContents());
	}

	/**
	 * ダンプ実行
	 *
	 * @access protected
	 * @return string 結果
	 */
	protected function dump () {
		$command = $this->createCommand('mysqldump');
		$command->push('--single-transaction');
		$command->push('--skip-dump-date');
		$command->setStderrRedirectable(true);
		if ($command->hasError()) {
			throw new DatabaseException($command->getResult()->join(' '));
		}
		return $command->getResult()->join("\n");
	}

	/**
	 * コマンドラインを返す
	 *
	 * @access protected
	 * @param string $command コマンド名
	 * @return CommandLine コマンドライン
	 */
	protected function createCommand ($command = 'mysql') {
		putenv('MYSQL_PWD=' . $this->dsn->decryptPassword());
		$command = new CommandLine('bin/' . $command);
		$command->setDirectory(FileUtils::getDirectory('mysql'));
		$command->push('--host=' . $this['host']->getAddress());
		$command->push('--user=' . $this['uid']);
		$command->push($this['database_name']);
		return $command;
	}

	/**
	 * 最適化
	 *
	 * @access public
	 */
	public function optimize () {
		foreach ($this->getTableNames() as $table) {
			if ($this->getTableProfile($table)->isOptimizable()) {
				$this->exec('OPTIMIZE TABLE ' . $table);
			} else {
				$this->log($table . 'テーブルは最適化できません。');
			}
		}
		$this->log($this . 'を最適化しました。');
	}

	/**
	 * 旧式か
	 *
	 * @access public
	 * @return boolean 旧式ならTrue
	 */
	public function isLegacy () {
		return ($this->getVersion() < 5);
	}
}
