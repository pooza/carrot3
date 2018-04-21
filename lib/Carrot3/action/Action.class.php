<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage action
 */

namespace Carrot3;

/**
 * アクション
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class Action implements HTTPRedirector, Assignable {
	use HTTPRedirectorObject, BasicObject, KeyGenerator;
	protected $name;
	protected $title;
	protected $url;
	protected $config;
	protected $module;
	protected $methods;
	protected $renderResource;
	const ACCESSOR = 'a';

	/**
	 * @access public
	 * @param Module $module 呼び出し元モジュール
	 */
	public function __construct (Module $module) {
		$this->module = $module;
	}

	/**
	 * @access public
	 * @param string $name プロパティ名
	 * @return mixed 各種オブジェクト
	 */
	public function __get (string $name) {
		switch ($name) {
			case 'loader':
				return Loader::getInstance();
			case 'controller':
			case 'request':
			case 'user':
				return Utils::executeMethod($this->loader->getClass($name), 'getInstance');
			case 'database':
				if ($table = $this->getModule()->getTable()) {
					return $table->getDatabase();
				}
				return Database::getInstance();
		}
	}

	/**
	 * 実行
	 *
	 * getRequestMethodsで指定されたメソッドでリクエストされた場合に実行される。
	 *
	 * @access public
	 * @return string ビュー名
	 * @abstract
	 */
	abstract public function execute ();

	/**
	 * executeメソッドを実行可能か？
	 *
	 * getDefaultViewに遷移すべきかどうかの判定。
	 * HEAD又は未定義メソッドの場合、GETとしてふるまう。
	 *
	 * @access public
	 * @return bool executeメソッドを実行可能ならTrue
	 */
	public function isExecutable ():bool {
		if (StringUtils::isBlank($method = $this->request->getMethod()) || ($method == 'HEAD')) {
			$method = 'GET';
		}
		return $this->getRequestMethods()->isContain($method);
	}

	/**
	 * キャッシュできるか？
	 *
	 * RenderManagerでレンダリング結果をキャッシュできるか。
	 *
	 * @access public
	 * @return bool キャッシュできるならTrue
	 */
	public function isCacheable ():bool {
		return false;
	}

	/**
	 * キャッシュされているか？
	 *
	 * RenderManagerでレンダリング結果がキャッシュされているか。
	 *
	 * @access public
	 * @return bool キャッシュされているならTrue
	 */
	public function isCached ():bool {
		return $this->isCacheable() && RenderManager::getInstance()->hasCache($this);
	}

	/**
	 * ダイジェストを返す
	 *
	 * @access public
	 * @return string ダイジェスト
	 */
	public function digest ():?string {
		return $this->createKey([
			$this->getModule()->getName(),
			$this->getName(),
		]);
	}

	/**
	 * 初期化
	 *
	 * Falseを返すと、例外が発生。
	 *
	 * @access public
	 * @return bool 正常終了ならTrue
	 */
	public function initialize () {
		if ($errors = $this->user->getAttribute('errors')) {
			$this->request->setErrors($errors);
			$this->user->removeAttribute('errors');
		}
		return true;
	}

	/**
	 * デフォルト時ビュー
	 *
	 * getRequestMethodsに含まれていないメソッドから呼び出されたとき、
	 * executeではなくこちらが実行される。
	 *
	 * @access public
	 * @return string ビュー名
	 */
	public function getDefaultView () {
		return View::SUCCESS;
	}

	/**
	 * エラー時処理
	 *
	 * バリデート結果が妥当でなかったときに実行される。
	 *
	 * @access public
	 * @return string ビュー名
	 */
	public function handleError () {
		return View::ERROR;
	}

	/**
	 * バリデータ登録
	 *
	 * 動的に登録しなければならないバリデータを、ここで登録。
	 * 動的に登録する必要のないバリデータは、バリデーション定義ファイルに記述。
	 *
	 * @access public
	 */
	public function registerValidators () {
	}

	/**
	 * 論理バリデーション
	 *
	 * registerValidatorsで吸収できない、複雑なバリデーションをここに記述。
	 * registerValidatorsで実現できないか、まずは検討すべき。
	 *
	 * @access public
	 * @return bool 妥当な入力ならTrue
	 */
	public function validate ():bool {
		return !$this->request->hasErrors();
	}

	/**
	 * 設定を返す
	 *
	 * @access public
	 * @param string $name 設定名
	 * @return mixed 設定値
	 */
	public function getConfig (string $name) {
		if (!$this->config) {
			$this->config = Tuple::create(
				$this->getModule()->getConfig($this->getName(), 'actions')
			);
		}
		return $this->config[$name];
	}

	/**
	 * アクション名を返す
	 *
	 * @access public
	 * @return string アクション名
	 */
	public function getName ():string {
		if (StringUtils::isBlank($this->name)) {
			if (!mb_ereg('\\\\([[:alnum:]]+)Action$', Utils::getClass($this), $matches)) {
				$message = new StringFormat('アクション "%s" の名前が正しくありません。');
				$message[] = Utils::getClass($this);
				throw new ModuleException($message);
			}
			$this->name = $matches[1];
		}
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
			$this->title = $this->getConfig('title');
		}
		return $this->title;
	}

	/**
	 * 属性値を全て返す
	 *
	 * @access public
	 * @return Tuple 属性値
	 */
	public function getAttributes ():Tuple {
		return Tuple::create([
			'name' => $this->getName(),
			'title' => $this->getTitle(),
		]);
	}

	/**
	 * モジュールを返す
	 *
	 * @access public
	 * @return Module モジュール
	 */
	public function getModule () {
		return $this->module;
	}

	/**
	 * ビューを返す
	 *
	 * @access public
	 * @param string $name ビュー名
	 * @return View ビュー
	 */
	public function getView (?string $name):View {
		if (StringUtils::isBlank($name) || ($this->request->getMethod() == 'HEAD')) {
			return new EmptyView($this, null);
		}

		$class = null;
		$queries = StringUtils::toLower([
			$this->getModule()->getNamespace() . '\\' . $this->getName() . $name . 'View',
			$this->getModule()->getNamespace() . '\\' . $this->getName() . 'View',
			$this->getViewClass(),
		]);
		foreach ($queries as $query) {
			try {
				if ($class = $this->loader->getClass($query)) {
					return new $class($this, $name, $this->request->getAttribute('renderer'));
				}
			} catch (LoaderException $e) {
				// LoaderExceptionは無視。
			}
		}
		throw new ViewException($this . 'のビューがありません。');
	}

	/**
	 * ビューのクラス名を返す
	 *
	 * @access protected
	 * @return string クラス名
	 */
	protected function getViewClass () {
		if (StringUtils::isBlank($class = $this->getConfig('view'))) {
			$class = 'SmartyView';
			if ($this->request->hasAttribute('renderer')) {
				$class = 'View';
			}
		}
		return $this->loader->getClass($class);
	}

	/**
	 * メモリ上限を返す
	 *
	 * @access public
	 * @return mixed memory_limitディレクティブにそのまま渡す。 -1, '128M' 等。
	 */
	public function getMemoryLimit () {
		return BS_APP_MEMORY_LIMIT;
	}

	/**
	 * タイムアウト時間を返す
	 *
	 * @access public
	 * @return int タイムアウト時間(秒)、制限しない場合は0
	 */
	public function getTimeLimit () {
		return BS_APP_TIME_LIMIT;
	}

	/**
	 * カレントレコードIDを返す
	 *
	 * Module::getRecordID()のエイリアス。
	 *
	 * @access public
	 * @return int カレントレコードID
	 * @final
	 */
	final public function getRecordID () {
		return $this->getModule()->getRecordID();
	}

	/**
	 * カレントレコードIDを設定
	 *
	 * Module::setRecordID()のエイリアス。
	 *
	 * @access public
	 * @param mixed $id カレントレコードID、又はレコード
	 * @final
	 */
	final public function setRecordID ($id) {
		$this->getModule()->setRecordID($id);
	}

	/**
	 * カレントレコードIDをクリア
	 *
	 * Module::clearRecordID()のエイリアス。
	 *
	 * @access public
	 * @final
	 */
	final public function clearRecordID () {
		$this->getModule()->clearRecordID();
	}

	/**
	 * 編集中レコードを返す
	 *
	 * @access public
	 * @return Record 編集中レコード
	 */
	public function getRecord () {
		return null;
	}

	/**
	 * テーブルを返す
	 *
	 * @access public
	 * @return TableHandler テーブル
	 */
	public function getTable () {
		return $this->getModule()->getTable();
	}

	/**
	 * 抽出条件を生成して返す
	 *
	 * @access protected
	 * @return Criteria 抽出条件
	 */
	protected function createCriteria () {
		return $this->database->createCriteria();
	}

	/**
	 * 必要なクレデンシャルを返す
	 *
	 * モジュール規定のクレデンシャル以外の、動的なクレデンシャルを設定。
	 * 必要がある場合、このメソッドをオーバライドする。
	 *
	 * @access public
	 * @return string 必要なクレデンシャル
	 */
	public function getCredential () {
		return $this->getModule()->getCredential();
	}

	/**
	 * クレデンシャルを持たないユーザーへの処理
	 *
	 * @access public
	 * @return string ビュー名
	 */
	public function deny () {
		return $this->controller->getAction('secure')->forward();
	}

	/**
	 * 正規なリクエストとして扱うメソッド
	 *
	 * ここに指定したリクエストではexecuteが、それ以外ではgetDefaultViewが実行される。
	 * 適宜オーバライド。
	 *
	 * @access public
	 * @return Tuple 許可されたメソッドの配列
	 */
	public function getRequestMethods () {
		if (!$this->methods) {
			$this->methods = Tuple::create();
			if ($file = $this->getValidationFile()) {
				$config = Tuple::create($file->getResult());
				if ($methods = $config['methods']) {
					$this->methods->merge($config['methods']);
					return $this->methods;
				}
			}
			$this->methods[] = 'GET';
			$this->methods[] = 'POST';
		}
		return $this->methods;
	}

	/**
	 * バリデーション設定ファイルを返す
	 *
	 * @access public
	 * @return ConfigFile バリデーション設定ファイル
	 */
	public function getValidationFile () {
		return $this->getModule()->getValidationFile($this->getName());
	}

	/**
	 * リダイレクト対象
	 *
	 * @access public
	 * @return URL
	 */
	public function getURL ():?HTTPURL {
		if (!$this->url) {
			$this->url = URL::create(null, 'carrot');
			$this->url['action'] = $this;
		}
		return $this->url;
	}

	/**
	 * 転送
	 *
	 * @access public
	 * @return string ビュー名
	 */
	public function forward () {
		if (!$this->initialize()) {
			throw new \BadFunctionCallException($this . 'が初期化できません。');
		}
		$this->controller->register($this);
		(new FilterSet)->execute();
		return View::NONE;
	}

	/**
	 * 状態オプションをアサインする
	 *
	 * @access protected
	 * @return string ビュー名
	 */
	protected function assignStatusOptions () {
		if ($table = $this->getModule()->getTable()) {
			$this->request->setAttribute('status_options', $table->getStatusOptions());
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
		return sprintf('%sのアクション"%s"', $this->getModule(), $this->getName());
	}
}
