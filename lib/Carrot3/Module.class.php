<?php
/**
 * @package jp.co.b-shock.carrot3
 */

namespace Carrot3;

/**
 * モジュール
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class Module implements HTTPRedirector, Assignable {
	use HTTPRedirectorMethods, BasicObject;
	protected $name;
	protected $title;
	protected $url;
	protected $directories;
	protected $actions;
	protected $config = [];
	protected $configFiles;
	protected $prefix;
	protected $record;
	protected $table;
	protected $params;
	protected $recordClass;
	static private $instances;
	static private $prefixes = [];
	const ACCESSOR = 'm';

	/**
	 * @access protected
	 * @param string $name モジュール名
	 */
	protected function __construct ($name) {
		$this->name = $name;
		$this->actions = Tuple::create();

		if (!$this->getDirectory()) {
			throw new Exception($this . 'のディレクトリが見つかりません。');
		}
		if ($file = $this->getConfigFile('module')) {
			$this->config = Tuple::create(ConfigManager::getInstance()->compile($file));
		}
		if ($file = $this->getConfigFile('filters')) {
			$this->config['filters'] = Tuple::create($file->getResult());
		}
	}

	private function createKey ($key) {
		return Crypt::digest([
			Utils::getClass($this),
			$key,
			$this->getName(),
		]);
	}

	/**
	 * フライウェイトインスタンスを返す
	 *
	 * @access public
	 * @param string $name モジュール名
	 * @static
	 */
	static public function getInstance ($name) {
		if (!self::$instances) {
			self::$instances = Tuple::create();
		}
		if (!self::$instances[$name]) {
			try {
				if ($class = Loader::getInstance()->getClass($name . 'module')) {
					$module = new $class($name);
				}
			} catch (LOaderException $e) {
				$module = new self($name);
			}
			self::$instances[$name] = $module;
		}
		return self::$instances[$name];
	}

	/**
	 * 属性値を全て返す
	 *
	 * @access public
	 * @return Tuple 属性値
	 */
	public function getAttributes () {
		return Tuple::create([
			'name' => $this->getName(),
			'title' => $this->getTitle(),
			'title_menu' => $this->getMenuTitle(),
			'record_class' => $this->getRecordClass(),
		]);
	}

	/**
	 * モジュール名を返す
	 *
	 * @access public
	 * @return string モジュール名
	 */
	public function getName () {
		return $this->name;
	}

	/**
	 * タイトルを返す
	 *
	 * @access public
	 * @return string タイトル
	 */
	public function getTitle () {
		if (StringUtils::isBlank($this->title)) {
			if (StringUtils::isBlank($title = $this->getConfig('title'))) {
				if (StringUtils::isBlank($title = $this->getRecordClass('ja'))) {
					$title = $this->getName();
				} else if ($this->isAdminModule()) {
					$title .= '管理';
				}
			}
			$this->title = mb_ereg_replace('モジュール$', '', $title) . 'モジュール';
		}
		return $this->title;
	}

	/**
	 * メニューでのタイトルを返す
	 *
	 * @access public
	 * @return string タイトル
	 */
	public function getMenuTitle () {
		if (StringUtils::isBlank($title = $this->getConfig('title_menu'))) {
			if (StringUtils::isBlank($title = $this->getConfig('title'))) {
				if (StringUtils::isBlank($title = $this->getRecordClass('ja'))) {
					$title = $this->getName();
				}
			}
		}
		return $title;
	}

	/**
	 * ディレクトリを返す
	 *
	 * @access public
	 * @param string $name ディレクトリ名
	 * @return Directory 対象ディレクトリ
	 */
	public function getDirectory ($name = 'module') {
		if (!$this->directories) {
			$this->directories = Tuple::create();
		}
		if (!$this->directories[$name]) {
			switch ($name) {
				case 'module':
					$dir = FileUtils::getDirectory('modules');
					$this->directories['module'] = $dir->getEntry($this->getName());
					break;
				default:
					$this->directories[$name] = $this->getDirectory('module')->getEntry($name);
					break;
			}
		}
		return $this->directories[$name];
	}

	/**
	 * 検索条件キャッシュを返す
	 *
	 * @access public
	 * @return Tuple 検索条件キャッシュ
	 */
	public function getParameterCache () {
		if (!$this->params) {
			$this->params = Tuple::create();
			if ($params = $this->user->getAttribute($this->createKey('parameters'))) {
				$this->params->setParameters($params);
			}
		}
		return $this->params;
	}

	/**
	 * 検索条件キャッシュを設定
	 *
	 * @access public
	 * @param Tuple $params 検索条件キャッシュ
	 */
	public function cacheParameters (Tuple $params) {
		$this->params = clone $params;
		$this->params->removeParameter(Module::ACCESSOR);
		$this->params->removeParameter(Action::ACCESSOR);
		$this->user->setAttribute($this->createKey('parameters'), $this->params);
	}

	/**
	 * 検索条件キャッシュをクリア
	 *
	 * @access public
	 */
	public function clearParameterCache () {
		$this->user->removeAttribute($this->createKey('parameters'));
	}

	/**
	 * テーブルを返す
	 *
	 * @access public
	 * @return TableHandler テーブル
	 */
	public function getTable () {
		if (!$this->table && !StringUtils::isBlank($class = $this->getRecordClass())) {
			$this->table = TableHandler::create($class);
		}
		return $this->table;
	}

	/**
	 * 編集中レコードを返す
	 *
	 * @access public
	 * @return Record 編集中レコード
	 */
	public function getRecord () {
		if (!$this->record && $this->getRecordID()) {
			$this->record = $this->getTable()->getRecord($this->getRecordID());
		}
		return $this->record;
	}

	/**
	 * カレントレコードIDを返す
	 *
	 * @access public
	 * @return int カレントレコードID
	 */
	public function getRecordID () {
		return $this->user->getAttribute($this->createKey('record'));
	}

	/**
	 * カレントレコードIDを設定
	 *
	 * @access public
	 * @param mixed $id カレントレコードID、又はレコード
	 */
	public function setRecordID ($id) {
		if ($id instanceof Record) {
			$id = $id->getID();
		} else if (is_iterable($id)) {
			$id = Tuple::create($id);
			$id = $id[$this->getTable()->getKeyField()];
		} else if (!is_numeric($id)) {
			$key = [$this->getTable()->getUniqueKeyField() => $id];
			if ($record = $this->getTable()->getRecord($key)) {
				$id = $record->getID();
			} else {
				$id = null;
			}
		}
		$this->user->setAttribute($this->createKey('record'), $id);
		$this->record = null;
	}

	/**
	 * カレントレコードIDをクリア
	 *
	 * @access public
	 */
	public function clearRecordID () {
		$this->user->removeAttribute($this->createKey('record'));
		$this->record = null;
	}

	/**
	 * 設定ファイルを返す
	 *
	 * @access public
	 * @param string $name ファイル名
	 * @return ConfigFile 設定ファイル
	 */
	public function getConfigFile ($name = 'module') {
		if (!$this->configFiles) {
			$this->configFiles = Tuple::create();
		}
		if (!$this->configFiles[$name]) {
			$finder = new FileFinder('ConfigFile');
			$finder->clearDirectories();
			$finder->registerDirectory($this->getDirectory());
			if ($dir = $this->getDirectory('config')) {
				$finder->registerDirectory($dir);
			}
			$finder->registerSuffix('yaml');
			$this->configFiles[$name] = $finder->execute($name);
		}
		return $this->configFiles[$name];
	}

	/**
	 * 設定値を返す
	 *
	 * @access public
	 * @param string $key キー名
	 * @param string $section セクション名
	 * @return string 設定値
	 */
	public function getConfig ($key, $section = 'module') {
		if (isset($this->config[$section][$key])) {
			return $this->config[$section][$key];
		}
	}

	/**
	 * バリデーション設定ファイルを返す
	 *
	 * @access public
	 * @param string $name 設定ファイルの名前
	 * @return ConfigFile バリデーション設定ファイル
	 */
	public function getValidationFile ($name) {
		if ($dir = $this->getDirectory('validate')) {
			return ConfigManager::getConfigFile($dir->getPath() . '/' . $name);
		}
	}

	/**
	 * アクションを返す
	 *
	 * @access public
	 * @param string $name アクション名
	 * @return Action アクション
	 */
	public function getAction ($name) {
		if (!$this->actions[$name]) {
			$class = $this->loader->getClass($this->getNamespace() . '\\' . $name . 'Action');
			$this->actions[$name] = new $class($this);
		}
		return $this->actions[$name];
	}

	/**
	 * クレデンシャルを返す
	 *
	 * @access public
	 * @return string クレデンシャル
	 */
	public function getCredential () {
		if ($file = $this->getConfigFile('filters')) {
			foreach ($file->getResult() as $section) {
				if ($section['class'] == 'SecurityFilter') {
					if (!StringUtils::isBlank($section['params']['credential'])) {
						return $section['params']['credential'];
					}
				}
			}
		}
		return $this->getPrefix();
	}

	/**
	 * モジュールが属する名前空間を返す
	 *
	 * @access public
	 * @return string 名前空間
	 */
	public function getNamespace () {
		return __NAMESPACE__ . '\\' . $this->getName() . 'Module';
	}

	/**
	 * モジュール名プレフィックスを返す
	 *
	 * @access public
	 * @return string モジュール名プレフィックス
	 */
	public function getPrefix () {
		if (!$this->prefix) {
			$pattern = '^(' . self::getPrefixes()->join('|') . ')';
			if (mb_ereg($pattern, $this->getName(), $matches)) {
				$this->prefix = $matches[1];
			}
		}
		return $this->prefix;
	}

	/**
	 * 管理者向けモジュールか？
	 *
	 * @access public
	 * @return bool 管理者向けモジュールならTrue
	 */
	public function isAdminModule () {
		return $this->getPrefix() == 'Admin';
	}

	/**
	 * リダイレクト対象
	 *
	 * @access public
	 * @return URL
	 */
	public function getURL () {
		if (!$this->url) {
			$this->url = URL::create(null, 'carrot');
			$this->url['module'] = $this;
		}
		return $this->url;
	}

	/**
	 * レコードクラス名を返す
	 *
	 * @access public
	 * @param string $lang 言語 - 翻訳が必要な場合
	 * @return string レコードクラス名
	 */
	public function getRecordClass ($lang = null) {
		if (!$this->recordClass) {
			if (StringUtils::isBlank($name = $this->getConfig('record_class'))) {
				$pattern = '^' . $this->getPrefix() . '([[:upper:]][[:alpha:]]+)$';
				if (mb_ereg($pattern, $this->getName(), $matches)) {
					$name = $matches[1];
				}
			}
			if (!StringUtils::isBlank($name)) {
				try {
					$this->recordClass = $this->loader->getClass($name);
				} catch (\Exception $e) {
					return null;
				}
			}
		}
		if (StringUtils::isBlank($lang)) {
			return $this->loader->getClass($this->recordClass);
		} else {
			return TranslateManager::getInstance()->execute(
				StringUtils::underscorize($this->recordClass)
			);
		}
	}

	/**
	 * アサインすべき値を返す
	 *
	 * @access public
	 * @return mixed アサインすべき値
	 */
	public function assign () {
		return $this->getAttributes();
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('モジュール "%s"', $this->getName());
	}

	/**
	 * 全てのモジュール名プレフィックスを配列で返す
	 *
	 * @access public
	 * @return Tuple モジュール名プレフィックス
	 * @static
	 */
	static public function getPrefixes () {
		if (!self::$prefixes) {
			self::$prefixes = StringUtils::explode(',', BS_MODULE_PREFIXES);
		}
		return self::$prefixes;
	}
}
