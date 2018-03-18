<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage database.database
 */

namespace Carrot3;

/**
 * データベース接続
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class Database extends \PDO implements \ArrayAccess, Assignable {
	protected $tables;
	protected $dsn;
	protected $version;
	protected $profiles;
	static private $instances;
	const WITHOUT_LOGGING = 1;
	const WITHOUT_SERIALIZE = 2;
	const WITHOUT_PARENT = 4;
	const RECONNECT = 1;

	/**
	 * フライウェイトインスタンスを返す
	 *
	 * @access public
	 * @param string $name データベース名
	 * @param int $flags フラグのビット列
	 *   self::RECONNECT 再接続（取扱注意。基本、使っちゃダメ。）
	 * @return Database インスタンス
	 * @static
	 */
	static public function getInstance (string $name = 'default', int $flags = 0) {
		if (!self::$instances) {
			self::$instances = Tuple::create();
		}
		if (!self::$instances[$name] || ($flags & self::RECONNECT)) {
			$dsn = (new ConstantHandler)['PDO_' .  $name . '_DSN'];
			if (mb_ereg('^([[:alnum:]]+):', $dsn, $matches)) {
				$class = Loader::getInstance()->getClass($matches[1] . 'DataSourceName');
				if (($dsn = new $class($dsn, $name)) && ($db = $dsn->connect())) {
					if ($db->isLegacy()) {
						throw new DatabaseException($db . 'は旧式です。');
					}
					return self::$instances[$name] = $db;
				}
			}
			$message = new StringFormat('"%s"のDSNが適切ではありません。');
			$message[] = $name;
			throw new DatabaseException($message);
		}
		return self::$instances[$name];
	}

	/**
	 * @access public
	 */
	public function __clone () {
		throw new \BadFunctionCallException(Utils::getClass($this) . 'はコピーできません。');
	}

	/**
	 * テーブル名のリストを配列で返す
	 *
	 * @access public
	 * @return Tuple テーブル名のリスト
	 * @abstract
	 */
	abstract public function getTableNames ();

	/**
	 * 属性値を返す
	 *
	 * @access public
	 * @param string $name 属性名
	 * @return mixed 属性値
	 */
	public function getAttribute ($name) {
		return $this->dsn[$name];
	}

	/**
	 * 属性を全て返す
	 *
	 * @access public
	 * @return Tuple 属性
	 */
	public function getAttributes () {
		return $this->dsn;
	}

	/**
	 * DSNを設定
	 *
	 * @access public
	 * @param DataSourceName $dsn DSN
	 */
	public function setDSN (DataSourceName $dsn) {
		$this->dsn = $dsn;
	}

	/**
	 * バージョンを返す
	 *
	 * @access public
	 * @return float バージョン
	 */
	public function getVersion () {
		if (!$this->version) {
			$this->version = \PDO::query('SELECT version() AS ver')->fetch()['ver'];
		}
		return $this->version;
	}

	/**
	 * クエリーを実行してPDOStatementを返す
	 *
	 * @access public
	 * @return PDOStatement
	 * @param string $query クエリー文字列
	 */
	public function query ($query) {
		if (!$rs = parent::query($query)) {
			$message = new StringFormat('実行不能なクエリーです。(%s) [%s]');
			$message[] = $this->getError();
			$message[] = $query;
			throw new DatabaseException($message);
		}
		$rs->setFetchMode(\PDO::FETCH_ASSOC);
		return $rs;
	}

	/**
	 * クエリーを実行
	 *
	 * @access public
	 * @return int 影響した行数
	 * @param string $query クエリー文字列
	 */
	public function exec ($query) {
		if (($r = parent::exec($query)) === false) {
			$message = new StringFormat('実行不能なクエリーです。(%s) [%s]');
			$message[] = $this->getError();
			$message[] = $query;
			throw new DatabaseException($message);
		}
		return $r;
	}

	/**
	 * 直近のエラーメッセージを返す
	 *
	 * @access public
	 * @return string エラーメッセージ
	 */
	public function getError () {
		$err = self::errorInfo();
		return StringUtils::convertEncoding($err[2]);
	}

	/**
	 * テーブルのプロフィールを返す
	 *
	 * @access public
	 * @param string $name テーブルの名前
	 * @return TableProfile テーブルのプロフィール
	 */
	public function getTableProfile (string $name) {
		if (!$this->profiles) {
			$this->profiles = Tuple::create();
		}
		if (!$this->profiles[$name]) {
			if (!mb_ereg('\\\\([[:alnum:]]+)Database$', Utils::getClass($this), $matches)) {
				throw new DatabaseException($this . 'のクラス名が正しくありません。');
			}
			$class = Loader::getInstance()->getClass($matches[1] . 'TableProfile');
			$this->profiles[$name] = new $class($name, $this);
		}
		return $this->profiles[$name];
	}

	/**
	 * 抽出条件オブジェクトを生成して返す
	 *
	 * @access public
	 * @return Criteria 抽出条件
	 */
	public function createCriteria () {
		$criteria = new Criteria;
		$criteria->setDatabase($this);
		return $criteria;
	}

	/**
	 * データベースのインスタンス名を返す
	 *
	 * @access public
	 * @return string インスタンス名
	 */
	public function getName () {
		return $this->dsn->getName();
	}

	/**
	 * 文字列をクォート
	 *
	 * @access public
	 * @param mixed $value 対象の文字列または配列
	 * @param string $type クォートのタイプ
	 * @return string クォート後の文字列
	 */
	public function quote ($value, $type = self::PARAM_STR) {
		if (is_iterable($value)) {
			$values = $value;
			foreach ($values as $key => $value) {
				$values[$key] = self::quote($value, $type);
			}
			return $values;
		} else if (StringUtils::isBlank($value)) {
			return 'NULL';
		} else {
			return parent::quote($value, $type);
		}
	}

	/**
	 * ログを書き込む
	 *
	 * @access public
	 * @param mixed $log ログメッセージの文字列、又はStringFormat
	 */
	public function log ($log) {
		if ($this->isLoggable()) {
			LogManager::getInstance()->put($log, $this);
		}
	}

	/**
	 * クエリーログを使用するか？
	 *
	 * @access protected
	 * @return bool クエリーログを使用するならTrue
	 */
	protected function isLoggable () {
		return !!$this->getAttribute('loggable');
	}

	/**
	 * 旧式か
	 *
	 * @access public
	 * @return bool 旧式ならTrue
	 */
	public function isLegacy () {
		return false;
	}

	/**
	 * 命名規則に従い、シーケンス名を返す
	 *
	 * @access public
	 * @param string $table テーブル名
	 * @param string $field 主キーフィールド名
	 * @return string シーケンス名
	 */
	public function getSequenceName ($table, $field = 'id') {
		return null;
	}

	/**
	 * テーブルを作成
	 *
	 * @access public
	 * @param string $table テーブル名
	 * @param Tuple $schema スキーマ
	 * @param int $flags フラグのビット列
	 *   SQL::TEMPORARY テンポラリテーブル
	 */
	public function createTable ($table, Tuple $schema, int $flags = 0) {
		$this->exec(SQL::getCreateTableQuery($table, $schema, $flags));
		$this->tables = null;
	}

	/**
	 * テーブルを削除
	 *
	 * @access public
	 * @param string $name テーブル名
	 */
	public function deleteTable ($table) {
		$this->exec(SQL::getDropTableQuery($table));
		$this->tables = null;
	}

	/**
	 * テーブルを削除
	 *
	 * deleteTableのエイリアス
	 *
	 * @access public
	 * @param string $table テーブル名
	 * @final
	 */
	final public function dropTable ($table) {
		$this->deleteTable($table);
	}

	/**
	 * インデックスを作成
	 *
	 * @access public
	 * @param string $table 対象テーブル
	 * @param Tuple $fields インデックスを構成するフィールドの配列
	 */
	public function createIndex ($table, Tuple $fields) {
	}

	/**
	 * ダンプファイル生成してを返す
	 *
	 * @access public
	 * @param Directory $dir 出力先ディレクトリ
	 * @return File ダンプファイル
	 */
	public function createDumpFile (Directory $dir = null) {
		if (!$dir) {
			$dir = FileUtils::getDirectory('dump');
		}

		try {
			$name = sprintf('%s_%s.sql', $this->getName(), Date::create()->format('Y-m-d'));
			$file = $dir->createEntry($name);
			$file->setContents($this->dump());
			$dir->purge();
		} catch (\Exception $e) {
			return;
		}

		$this->log($this . 'のダンプファイルを保存しました。');
		return $file;
	}

	/**
	 * バックアップ対象ファイルを返す
	 *
	 * @access public
	 * @return File バックアップ対象ファイル
	 */
	public function getBackupTarget () {
		return $this->createDumpFile();
	}

	/**
	 * ダンプ実行
	 *
	 * @access protected
	 * @return string 結果
	 */
	protected function dump () {
		throw new DatabaseException($this . 'はダンプできません。');
	}

	/**
	 * 最適化
	 *
	 * @access public
	 */
	public function optimize () {
	}

	/**
	 * 最適化
	 *
	 * optimizeのエイリアス
	 *
	 * @access public
	 * @final
	 */
	final public function vacuum () {
		return $this->optimize();
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 * @return bool 要素が存在すればTrue
	 */
	public function offsetExists ($key) {
		return $this->dsn->hasParameter($key);
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 * @return mixed 要素
	 */
	public function offsetGet ($key) {
		return $this->getAttribute($key);
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 * @param mixed 要素
	 */
	public function offsetSet ($key, $value) {
		throw new DatabaseException('データベースの属性を直接更新することはできません。');
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 */
	public function offsetUnset ($key) {
		throw new DatabaseException('データベースの属性は削除できません。');
	}

	/**
	 * 外部キーが有効か？
	 *
	 * @access public
	 * @return bool 有効ならTrue
	 */
	public function hasForeignKey () {
		return true;
	}

	/**
	 * リストア可能？
	 *
	 * @access public
	 * @return bool 可能ならTrue
	 */
	public function isRestoreable () {
		return false;
	}

	/**
	 * アサインすべき値を返す
	 *
	 * @access public
	 * @return mixed アサインすべき値
	 */
	public function assign () {
		$values = [
			'name' => $this->getName(),
			'tables' => $this->getTableNames()->getParameters(),
		];
		foreach ($this->getAttributes() as $key => $value) {
			if (in_array($key, ['uid', 'password', 'user'])) {
				continue;
			} else if ($value instanceof File) {
				$values['attributes'][$key] = $value->getPath();
			} else if ($value instanceof Host) {
				$values['attributes'][$key] = $value->getName();
			} else {
				$values['attributes'][$key] = $value;
			}
		}
		return $values;
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
	public function getFunction (string $name, $value, bool $quotes = false) {
		$func = new StringFormat('%s(%s)');
		$func[] = $name;
		if (!!$quotes) {
			$func[] = $this->quote($value);
		} else {
			$func[] = $value;
		}
		return $func->getContents();
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('データベース "%s"', $this->getName());
	}

	/**
	 * データベース情報のリストを返す
	 *
	 * @access public
	 * @return Tuple データベース情報
	 * @static
	 */
	static public function getDatabases () {
		$databases = Tuple::create();
		foreach (new ConstantHandler as $key => $value) {
			$pattern = '^' . ConstantHandler::PREFIX . '_PDO_([[:upper:]]+)_DSN$';
			if (mb_ereg($pattern, $key, $matches)) {
				$name = StringUtils::toLower($matches[1]);
				try {
					$databases[$name] = self::getInstance($name)->getAttributes();
				} catch (DatabaseException $e) {
				}
			}
		}
		return $databases;
	}
}
