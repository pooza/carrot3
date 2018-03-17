<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage database.database
 */

namespace Carrot3;

/**
 * SQLiteデータベース
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class SQLiteDatabase extends Database {

	/**
	 * テーブル名のリストを配列で返す
	 *
	 * @access public
	 * @return Tuple テーブル名のリスト
	 */
	public function getTableNames () {
		if (!$this->tables) {
			$this->tables = Tuple::create();
			$query = SQL::getSelectQuery(
				'name',
				'sqlite_master',
				'name NOT LIKE ' . $this->quote('sqlite_%')
			);
			foreach ($this->query($query) as $row) {
				$this->tables[] = $row['name'];
			}
		}
		return $this->tables;
	}

	/**
	 * ダンプ実行
	 *
	 * @access protected
	 * @return string 結果
	 */
	protected function dump () {
		$command = $this->createCommand();
		$command->push('.dump');
		if ($command->hasError()) {
			throw new DatabaseException($command->getResult());
		}
		return $command->getResult()->join("\n");
	}

	/**
	 * バックアップ対象ファイルを返す
	 *
	 * @access public
	 * @return File バックアップ対象ファイル
	 */
	public function getBackupTarget () {
		return $this['file'];
	}

	/**
	 * コマンドラインを返す
	 *
	 * @access protected
	 * @param string $command コマンド名
	 * @return CommandLine コマンドライン
	 */
	protected function createCommand ($command = 'sqlite3') {
		$command = new CommandLine('bin/' . $command);
		$command->setDirectory(FileUtils::getDirectory('sqlite3'));
		$command->push($this['file']->getPath());
		return $command;
	}

	/**
	 * 最適化
	 *
	 * @access public
	 */
	public function optimize () {
		$this->exec('VACUUM');
		$this->log($this . 'を最適化しました。');
	}

	/**
	 * データベース関数を返す
	 *
	 * @access public
	 * @param string $name 関数名
	 * @param string $value 値
	 * @param bool $quotes クォートする
	 * @return string 関数の記述
	 */
	public function getFunction ($name, $value, bool $quotes = false) {
		switch ($name) {
			case 'year':
				$func = new StringFormat('strftime(\'%%Y\', %s)');
				break;
			case 'month':
				$func = new StringFormat('strftime(\'%%m\', %s)');
				break;
			default:
				return parent::getFunction($name, $value, $quotes);
		}

		if (!!$quotes) {
			$func[] = $this->quote($value);
		} else {
			$func[] = $value;
		}
		return $func->getContents();
	}

	/**
	 * 外部キーが有効か？
	 *
	 * @access public
	 * @return bool 有効ならTrue
	 */
	public function hasForeignKey () {
		return false;
	}

	/**
	 * リストア可能？
	 *
	 * @access public
	 * @return bool 可能ならTrue
	 */
	public function isRestoreable () {
		return true;
	}

	/**
	 * バージョンを返す
	 *
	 * @access public
	 * @return float バージョン
	 */
	public function getVersion () {
		if (!$this->version && extension_loaded('sqlite3')) {
			$this->version = SQLite3::version()['versionString'];
		}
		return $this->version;
	}
}
