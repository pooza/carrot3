<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage database.table
 */

namespace Carrot3;

/**
 * データベーステーブル
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class TableHandler implements \IteratorAggregate, Dictionary, Assignable {
	use BasicObject;
	private $fields;
	private $criteria;
	private $order;
	private $page;
	private $pagesize = 20;
	private $lastpage;
	private $executed = false;
	private $result = [];
	private $queryString;
	private $recordClass;
	private $name;
	private $fieldNames = [];
	private $ids;
	const CLASS_SUFFIX = 'Handler';

	/**
	 * @access public
	 * @param mixed $criteria 抽出条件
	 * @param mixed $order ソート順
	 */
	public function __construct ($criteria = null, $order = null) {
		$this->fields = new FieldSet;
		$this->criteria = $this->createCriteria();
		$this->order = new FieldSet;
		$this->setCriteria($criteria);
		$this->setOrder($order);
		$this->setFields('*');

		if (!$this->isExists() && $this->getSchema()) {
			$this->createTable();
		}
	}

	/**
	 * @access public
	 */
	public function __clone () {
		$this->fields = clone $this->fields;
		$this->criteria = clone $this->criteria;
		$this->order = clone $this->order;
		if ($this->ids) {
			$this->ids = clone $this->ids;
		}
	}

	/**
	 * 出力フィールド文字列を返す
	 *
	 * @access public
	 * @return FieldSet 出力フィールド
	 */
	public function getFields () {
		return $this->fields;
	}

	/**
	 * 出力フィールド文字列を設定
	 *
	 * @access public
	 * @param mixed $fields 配列または文字列による出力フィールド
	 */
	public function setFields ($fields) {
		if (!$fields) {
			return;
		}
		if ($fields instanceof FieldSet) {
			$fields = clone $fields;
		} else {
			$fields = new FieldSet($fields);
		}
		$this->fields = $fields;
		$this->setExecuted(false);
	}

	/**
	 * 主キーフィールド名を返す
	 *
	 * @access public
	 * @return string 主キーフィールド名
	 */
	public function getKeyField () {
		return 'id';
	}

	/**
	 * ユニークキーフィールド名を返す
	 *
	 * @access public
	 * @return string ユニークキーフィールド名
	 */
	public function getUniqueKeyField () {
		return 'code';
	}

	/**
	 * 状態フィールド名
	 *
	 * @access public
	 * @return string 状態フィールド名
	 */
	public function getStatusField () {
		return 'status';
	}

	/**
	 * 作成日フィールド名
	 *
	 * @access public
	 * @return string 作成日フィールド名
	 */
	public function getCreateDateField () {
		return 'create_date';
	}

	/**
	 * 更新日フィールド名
	 *
	 * @access public
	 * @return string 更新日フィールド名
	 */
	public function getUpdateDateField () {
		return 'update_date';
	}

	/**
	 * ユーザーエージェントフィールド名
	 *
	 * @access public
	 * @return string ユーザーエージェントフィールド名
	 */
	public function getUserAgentField () {
		return 'useragent';
	}

	/**
	 * リモートホストフィールド名
	 *
	 * @access public
	 * @return string リモートホストフィールド名
	 */
	public function getRemoteHostField () {
		return 'remotehost';
	}

	/**
	 * リモートアドレスフィールド名
	 *
	 * @access public
	 * @return string リモートアドレスフィールド名
	 */
	public function getRemoteAddressField () {
		return 'remoteaddr';
	}

	/**
	 * 抽出条件を返す
	 *
	 * @access public
	 * @return Criteria 抽出条件
	 */
	public function getCriteria () {
		return $this->criteria;
	}

	/**
	 * 抽出条件文字列を設定
	 *
	 * @access public
	 * @param mixed $criteria 配列または文字列による抽出条件
	 */
	public function setCriteria ($criteria) {
		if (!$criteria) {
			return;
		}
		if ($criteria instanceof Criteria) {
			$this->criteria = clone $criteria;
		} else {
			$this->criteria = new Criteria($criteria);
		}
		$this->criteria->setDatabase($this->getDatabase());
		$this->setExecuted(false);
	}

	/**
	 * ソート順文字列を返す
	 *
	 * @access public
	 * @return BSFieldSet ソート順文字列
	 */
	public function getOrder () {
		return $this->order;
	}

	/**
	 * ソート順文字列を設定
	 *
	 * @access public
	 * @param mixed $order 配列または文字列によるソート順
	 */
	public function setOrder ($order) {
		if (!$order) {
			return;
		}
		if ($order instanceof FieldSet) {
			$this->order = clone $order;
		} else {
			$this->order = new FieldSet($order);
		}
		if (!$this->order->count()) {
			$this->order[] = $this->getKeyField();
		}
		$this->setExecuted(false);
	}

	/**
	 * ページ番号を返す
	 *
	 * @access public
	 * @return integer ページ番号
	 */
	public function getPageNumber () {
		return $this->page;
	}

	/**
	 * ページ番号を設定
	 *
	 * @access public
	 * @param integer $page ページ番号
	 */
	public function setPageNumber ($page = null) {
		if (!$page) {
			//何もしない
		} else if ($this->getLastPageNumber() < $page) {
			$page = $this->getLastPageNumber();
		} else if ($page < 1) {
			$page = 1;
		}
		$this->page = $page;
		$this->setExecuted(false);
	}

	/**
	 * ページサイズを返す
	 *
	 * @access public
	 * @return integer ページサイズ
	 */
	public function getPageSize () {
		return $this->pagesize;
	}

	/**
	 * ページ番号を設定
	 *
	 * @access public
	 * @param integer $pagesize ページサイズ
	 */
	public function setPageSize ($pagesize) {
		if (1 < $pagesize) {
			$this->pagesize = $pagesize;
			$this->setExecuted(false);
		}
	}

	/**
	 * 上位のレコードを返す
	 *
	 * @access public
	 * @param integer $limit 件数
	 * @return TableHandler 上位のレコード
	 */
	public function getRecent ($limit) {
		$table = clone $this;
		$table->setPageNumber(1);
		$table->setPageSize($limit);
		return $table;
	}

	/**
	 * テーブルは存在するか？
	 *
	 * @access public
	 * @return boolean 存在するならTrue
	 */
	public function isExists () {
		return $this->getDatabase()->getTableNames()->isContain($this->getName());
	}

	/**
	 * データベースを返す
	 *
	 * @access public
	 * @return Database データベース
	 */
	public function getDatabase () {
		return Database::getInstance();
	}

	/**
	 * 抽出条件を生成して返す
	 *
	 * @access protected
	 * @return Criteria 抽出条件
	 */
	protected function createCriteria () {
		return $this->getDatabase()->createCriteria();
	}

	/**
	 * レコードを返す
	 *
	 * @access public
	 * @param mixed[] $key 検索条件
	 * @return Record レコード
	 */
	public function getRecord ($key) {
		if (is_array($key) || ($key instanceof ParameterHolder)) {
			$key = Tuple::create($key);
		} else {
			$key = Tuple::create([$this->getKeyField() => $key]);
		}

		$class = $this->loader->getClass($this->getRecordClass());
		$record = new $class($this);
		if ($this->isExecuted()) {
			foreach ($this->getResult() as $row) {
				foreach ($key as $field => $value) {
					if ($row[$field] != $value) {
						continue 2;
					}
				}
				return $record->initialize($row);
			}
		} else {
			$table = clone $this;
			foreach ($key as $field => $value) {
				$table->getCriteria()->register($field, $value);
			}
			if ($table->count() == 1) {
				$table->query();
				return $record->initialize($table->result[0]);
			}
		}
	}

	/**
	 * レコード追加
	 *
	 * @access public
	 * @param mixed $values 値
	 * @param integer $flags フラグのビット列
	 *   Database::WITH_LOGGING ログを残さない
	 * @return string レコードの主キー
	 */
	public function createRecord ($values, $flags = 0) {
		if (!$this->isInsertable()) {
			throw new DatabaseException($this . 'へのレコード挿入はできません。');
		}

		$db = $this->getDatabase();
		$db->exec(SQL::getInsertQuery($this, $this->applySmartFields($values), $db));
		if ($this->hasSurrogateKey()) {
			$id = $db->lastInsertId($db->getSequenceName($this->getName(), $this->getKeyField()));
		} else {
			$id = $values[$this->getKeyField()];
		}
		$this->setExecuted(false);
		if (!($flags & Database::WITHOUT_LOGGING)) {
			$message = new StringFormat('%s(%s)を作成しました。');
			$message[] = TranslateManager::getInstance()->execute($this->getName());
			$message[] = $id;
			$db->log($message);
		}
		return $id;
	}

	/**
	 * レコード追加
	 *
	 * createRecordのエイリアス
	 *
	 * @access public
	 * @param mixed[] $values 値
	 * @param integer $flags フラグのビット列
	 *   Database::WITH_LOGGING ログを残さない
	 * @return string レコードの主キー
	 * @final
	 */
	final public function insertRecord ($values, $flags = 0) {
		return $this->createRecord($values, $flags);
	}

	/**
	 * スマートフィールドを適用
	 *
	 * @access protected
	 * @param array $values 処理対象
	 * @return Tuple 適用後の値
	 */
	protected function applySmartFields ($values) {
		$values = Tuple::create($values);
		$fields = $this->getProfile()->getFields();
		$smartFields = Tuple::create([
			$this->getCreateDateField() => Date::create()->format('Y-m-d H:i:s'),
			$this->getUpdateDateField() => Date::create()->format('Y-m-d H:i:s'),
			$this->getUserAgentField() => $this->request->getUserAgent()->getName(),
			$this->getRemoteHostField() => $this->request->getHost()->getName(),
			$this->getRemoteAddressField() => $this->request->getHost()->getAddress(),
		]);
		foreach ($smartFields as $key => $value) {
			if (!$values->hasParameter($key) && !!$fields[$key]) {
				$values[$key] = $value;
			}
		}
		return $values;
	}

	/**
	 * レコード追加可能か？
	 *
	 * @access protected
	 * @return boolean レコード追加可能ならTrue
	 */
	protected function isInsertable () {
		return false;
	}

	/**
	 * サロゲートキーを持つテーブルか？
	 *
	 * @access protected
	 * @return boolean サロゲートキーを持つならTrue
	 */
	protected function hasSurrogateKey () {
		return $this->isInsertable();
	}

	/**
	 * 全消去
	 *
	 * @access public
	 */
	public function clear () {
		if (!$this->isClearable()) {
			throw new DatabaseException($this . 'のレコード全消去はできません。');
		}
		$this->getDatabase()->exec('DELETE FROM ' . $this->getName());

		if ($dir = $this->getDirectory()) {
			$dir->clear();
		}
	}

	/**
	 * レコードの全消去が可能か？
	 *
	 * @access protected
	 * @return boolean レコード追加可能ならTrue
	 */
	protected function isClearable () {
		return false;
	}

	/**
	 * クエリーは実行されたか？
	 *
	 * @access protected
	 * @return boolean 実行されたならTrue
	 */
	protected function isExecuted () {
		return $this->executed;
	}

	/**
	 * クエリー実行フラグを設定
	 *
	 * @access protected
	 * @param boolean $executed クエリー実行フラグ
	 */
	protected function setExecuted ($executed) {
		if (!$this->executed = $executed) {
			$this->queryString = null;
			$this->result = [];
		}
	}

	/**
	 * @access public
	 * @return TableIterator イテレータ
	 */
	public function getIterator () {
		return new TableIterator($this);
	}

	/**
	 * テーブルを作成
	 *
	 * @access protected
	 */
	protected function createTable () {
		if ($this->isExists()) {
			throw new DatabaseException($this . 'は既に存在します。');
		}
		if ($schema = $this->getSchema()) {
			$flags = null;
			if ($this->isTemporary()) {
				$flags |= SQL::TEMPORARY;
			}
			$this->getDatabase()->createTable($this->getName(), $schema, $flags);
		}
	}

	/**
	 * フィールドを作成
	 *
	 * @access protected
	 * @param string $name フィールド名
	 * @param string $definition フィールドの定義内容
	 */
	protected function createField ($name, $definition) {
		if (!$this->isTemporary()) {
			throw new DatabaseException('フィールドは一時テーブルにしか追加できません。');
		}
		$query = new StringFormat('ALTER TABLE %s ADD COLUMN %s %s');
		$query[] = $this->getName();
		$query[] = $name;
		$query[] = $definition;
		$this->getDatabase()->exec($query->getContents());
	}

	/**
	 * インデックスを作成
	 *
	 * @access protected
	 * @param mixed $fields インデックスを構成するフィールドの配列、又はParameterHolder
	 */
	protected function createIndex ($fields) {
		if (!$this->isTemporary()) {
			throw new DatabaseException('インデックスは一時テーブルにしか追加できません。');
		}
		$this->getDatabase()->createIndex($this->getName(), Tuple::create($fields));
	}

	/**
	 * テンポラリテーブルか？
	 *
	 * @return boolean テンポラリテーブルならTrue
	 */
	protected function isTemporary () {
		return false;
	}

	/**
	 * プロフィールを返す
	 *
	 * @access public
	 * @return TableProfile
	 */
	public function getProfile () {
		return $this->getDatabase()->getTableProfile($this->getName());
	}

	/**
	 * 結果を返す
	 *
	 * @access public
	 * @return string[] 結果の配列
	 */
	public function getResult () {
		if (!$this->isExecuted()) {
			$this->query();
		}
		return $this->result;
	}

	/**
	 * クエリーを送信して結果を返す
	 *
	 * @access public
	 * @return string[] 結果の配列
	 */
	public function query () {
		$this->queryString = null;
		$this->result = $this->getDatabase()->query($this->getQuery())->fetchAll();
		$this->setExecuted(true);
		return $this->result;
	}

	/**
	 * @access public
	 * @return integer レコード数
	 */
	public function count () {
		if (!$this->getPageNumber()) {
			return $this->countAll();
		}
		return count($this->getResult());
	}

	/**
	 * 全てのレコード数を返す
	 *
	 * ページングしていても、全てのレコード数を返す。
	 *
	 * @access public
	 * @return integer 全てのレコード数
	 */
	public function countAll () {
		$sql = SQL::getSelectQuery(
			'count(*) AS cnt',
			$this->getName(),
			$this->getCriteria()
		);
		$row = $this->getDatabase()->query($sql)->fetch();
		return $row['cnt'];
	}

	/**
	 * クエリー文字列を返す
	 *
	 * @access public
	 * @return string クエリー文字列
	 */
	public function getQuery () {
		if (!$this->queryString) {
			if (!!$this->getPageNumber()) {
				$this->queryString = SQL::getSelectQuery(
					$this->getFields(),
					$this->getName(),
					$this->getCriteria(),
					$this->getOrder(),
					null,
					$this->getPageNumber(),
					$this->getPageSize()
				);
			} else {
				$this->queryString = SQL::getSelectQuery(
					$this->getFields(),
					$this->getName(),
					$this->getCriteria(),
					$this->getOrder()
				);
			}
		}
		return $this->queryString;
	}

	/**
	 * ページ数を返す
	 *
	 * @access public
	 * @return integer ページ数
	 */
	public function getLastPageNumber () {
		if (!$this->lastpage) {
			if ($page = ceil($this->countAll() / $this->getPageSize())) {
				$this->lastpage = $page;
			} else {
				$this->lastpage = 1;
			}
		}
		return $this->lastpage;
	}

	/**
	 * 最終ページか？
	 *
	 * @access public
	 * @return boolean 最終ページならTrue
	 */
	public function isLastPage () {
		return $this->getPageNumber() == $this->getLastPageNumber();
	}

	/**
	 * 現在の抽出条件で抽出して、配列で返す
	 *
	 * @access public
	 * @param string $language 言語
	 * @return string[] ラベルの配列
	 */
	public function getLabels ($language = 'ja') {
		$labels = [];
		foreach ($this as $record) {
			$labels[$record->getID()] = $record->getLabel($language);
		}
		return $labels;
	}

	/**
	 * フィールド名の配列を返す
	 *
	 * @access public
	 * @param string $language 言語
	 * @return string[] フィールド名の配列
	 */
	public function getFieldNames ($language = 'ja') {
		if (!$this->fieldNames) {
			if ($result = $this->getResult()) {
				$translator = TranslateManager::getInstance();
				foreach ($result[0] as $key => $value) {
					$this->fieldNames[$key] = $translator->execute($key, $language);
				}
			}
		}
		return $this->fieldNames;
	}

	/**
	 * 全ての主キーを返す
	 *
	 * @access public
	 * @return Tuple 主キーの配列
	 */
	public function getIDs () {
		if (!$this->ids) {
			$this->ids = Tuple::create();
			$sql = SQL::getSelectQuery(
				$this->getKeyField(),
				$this->getName(),
				$this->getCriteria(),
				$this->getOrder(),
				$this->getKeyField()
			);
			foreach ($this->getDatabase()->query($sql) as $row) {
				$this->ids[] = $row[$this->getKeyField()];
			}
		}
		return $this->ids;
	}

	/**
	 * 更新日付を返す
	 *
	 * @access public
	 * @return Date 更新日付
	 */
	public function getUpdateDate () {
		$date = null;
		foreach ($this as $record) {
			if (!$date || ($date < $record->getUpdateDate())) {
				$date = $record->getUpdateDate();
			}
		}
		return $date;
	}

	/**
	 * テーブル名を返す
	 *
	 * @access public
	 * @return string テーブル名
	 */
	public function getName () {
		if (!$this->name) {

			$this->name = StringUtils::underscorize(
				str_replace(self::CLASS_SUFFIX, '', Utils::getShortClass($this))
			);
		}
		return $this->name;
	}

	/**
	 * レコードクラス名を返す
	 *
	 * @access protected
	 * @return string レコードクラス名
	 */
	protected function getRecordClass () {
		if (!$this->recordClass) {
			$pattern = '\\\\([[:alnum:]]+)' . self::CLASS_SUFFIX . '$';
			if (mb_ereg($pattern, Utils::getClass($this), $matches)) {
				$this->recordClass = $this->loader->getClass($matches[1]);
			} else {
				throw new DatabaseException(Utils::getClass($this) . 'のクラス名が正しくありません。');
			}
		}
		return $this->recordClass;
	}

	/**
	 * ディレクトリを返す
	 *
	 * @access public
	 * @return Directory ディレクトリ
	 */
	public function getDirectory () {
		try {
			return FileUtils::getDirectory($this->getName());
		} catch (FileException $e) {
		}
	}

	/**
	 * 翻訳して返す
	 *
	 * @access public
	 * @param string $label ラベル
	 * @param string $language 言語
	 * @return string 翻訳された文字列
	 */
	public function translate ($label, $language) {
		if ($record = $this->getRecord($label)) {
			return $record->getLabel($language);
		}
	}

	/**
	 * 辞書の名前を返す
	 *
	 * @access public
	 * @return string 辞書の名前
	 */
	public function getDictionaryName () {
		return Utils::getClass($this);
	}

	/**
	 * アサインすべき値を返す
	 *
	 * @access public
	 * @return mixed アサインすべき値
	 */
	public function assign () {
		return $this->getLabels();
	}

	/**
	 * スキーマを返す
	 *
	 * @access public
	 * @return Tuple フィールド情報の配列
	 */
	public function getSchema () {
		return null;
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		try {
			$word = TranslateManager::getInstance()->execute($this->getName());
		} catch (TranslateException $e) {
			$word = $this->getName();
		}
		return $word . 'テーブル';
	}

	/**
	 * 子クラスを返す
	 *
	 * @access public
	 * @return Tuple 子クラス名の配列
	 * @static
	 */
	static public function getChildClasses () {
		return Tuple::create();
	}

	/**
	 * 画像のサイズ名を全てを返す
	 *
	 * @access public
	 * @return Tuple 画像のサイズ名
	 * @static
	 */
	static public function getImageNames () {
		return Tuple::create();
	}

	/**
	 * 添付ファイル名を全てを返す
	 *
	 * @access public
	 * @return Tuple 添付ファイル名
	 * @static
	 */
	static public function getAttachmentNames () {
		return Tuple::create();
	}

	/**
	 * テーブルハンドラを生成して返す
	 *
	 * @access public
	 * @param string $class レコード用クラス名、又はテーブル名
	 * @return TableHandler テーブルハンドラ
	 * @static
	 */
	static public function create ($class) {
		$table = Loader::getInstance()->createObject($class . self::CLASS_SUFFIX);
		if ($table instanceof TableHandler) {
			return $table;
		}
		throw new DatabaseException($class . 'はテーブルハンドラではありません。');
	}

	/**
	 * 全ステータスを返す
	 *
	 * @access public
	 * @param mixed[] $values 値
	 * @static
	 */
	static public function getStatusOptions () {
		return TranslateManager::getInstance()->getHash(
			['show', 'hide']
		);
	}
}
