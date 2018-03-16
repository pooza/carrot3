<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage controller
 */

namespace Carrot3;

/**
 * Carrotアプリケーションコントローラ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class Controller {
	use Singleton, BasicObject;
	protected $host;
	protected $platform;
	protected $headers;
	protected $actions;
	protected $searchDirectories;
	protected $serializeHandler;
	const ACTION_REGISTER_LIMIT = 20;
	const COMPLETED = true;

	/**
	 * @access protected
	 */
	protected function __construct () {
		$this->headers = Tuple::create();
		$this->actions = Tuple::create();
	}

	/**
	 * シングルトンインスタンスを返す
	 *
	 * @access public
	 * @return Controller インスタンス
	 * @static
	 */
	static public function getInstance () {
		if (!self::$instance) {
			if (PHP_SAPI == 'cli') {
				self::$instance = new self;
			} else {
				self::$instance = new WebController;
			}
		}
		return self::$instance;
	}

	/**
	 * ディスパッチ
	 *
	 * @access public
	 */
	public function dispatch () {
		if (StringUtils::isBlank($module = $this->request[Module::ACCESSOR])) {
			$module = BS_MODULE_DEFAULT_MODULE;
		}
		if (StringUtils::isBlank($action = $this->request[Action::ACCESSOR])) {
			$action = BS_MODULE_DEFAULT_ACTION;
		}

		try {
			$module = Module::getInstance($module);
			$action = $module->getAction($action);
		} catch (\Exception $e) {
			$action = $this->getAction('not_found');
		}
		$action->forward();
	}

	/**
	 * サーバホストを返す
	 *
	 * @access public
	 * @return string サーバホスト
	 */
	public function getHost () {
		if (!$this->host) {
			$this->host = new Host($this->getAttribute('SERVER_NAME'));
		}
		return $this->host;
	}

	/**
	 * サーバプラットフォームを返す
	 *
	 * @access public
	 * @return string サーバホスト
	 */
	public function getPlatform () {
		if (!$this->platform) {
			if (($os = PHP_OS) == 'Linux') {
				$file = new File('/usr/bin/apt-get');
				if ($file->isExists()) {
					$os = 'Debian';
				}
			}
			$this->platform = Platform::create($os);
		}
		return $this->platform;
	}

	/**
	 * モジュールを返す
	 *
	 * @access public
	 * @param string $name モジュール名
	 * @return Module モジュール
	 */
	public function getModule ($name = null) {
		if (StringUtils::isBlank($name)) {
			if ($action = $this->getAction()) {
				return $action->getModule();
			}
			$name = $this->request[Module::ACCESSOR];
		}
		return Module::getInstance($name);
	}

	/**
	 * アクションスタックを返す
	 *
	 * @access public
	 * @return Tuple アクションスタック
	 */
	public function getActionStack () {
		return $this->actions;
	}

	/**
	 * アクションをアクションスタックに加える
	 *
	 * @access public
	 * @param Action $action アクション
	 */
	public function register (Action $action) {
		if (self::ACTION_REGISTER_LIMIT < $this->getActionStack()->count()) {
			throw new \BadFunctionCallException('フォワードが多すぎます。');
		}
		$this->getActionStack()->push($action);
	}

	/**
	 * 特別なアクションを返す
	 *
	 * @access public
	 * @param string $name アクション名
	 * @return Action 名前で指定されたアクション、指定なしの場合は呼ばれたアクション
	 */
	public function getAction ($name = null) {
		if (StringUtils::isBlank($name)) {
			return $this->getActionStack()->getIterator()->getLast();
		}
		if ($module = $this->getModule((new ConstantHandler)['MODULE_' . $name . '_MODULE'])) {
			return $module->getAction((new ConstantHandler)['MODULE_' . $name . '_ACTION']);
		}
	}

	/**
	 * 属性を返す
	 *
	 * @access public
	 * @param string $name 属性の名前
	 */
	public function getAttribute ($name) {
		$env = Tuple::create();
		$env->setParameters(filter_input_array(INPUT_ENV));
		$env->setParameters($_SERVER);
		$keys = Tuple::create([
			$name,
			'HTTP_' . $name,
			'HTTP_' . str_replace('-', '_', $name),
		]);
		$keys->uniquize();
		foreach ($keys as $key) {
			if (!StringUtils::isBlank($value = $env[$key])) {
				return $value;
			}
		}
		return (new ConstantHandler)[$name];
	}

	/**
	 * 検索対象ディレクトリを返す
	 *
	 * @access public
	 * @return Tuple ディレクトリの配列
	 */
	public function getSearchDirectories () {
		if (!$this->searchDirectories) {
			$this->searchDirectories = Tuple::create();
			$this->searchDirectories[] = FileUtils::getDirectory('root');
		}
		return $this->searchDirectories;
	}

	/**
	 * レスポンスヘッダを返す
	 *
	 * @access public
	 * @return Tuple レスポンスヘッダの配列
	 */
	public function getHeaders () {
		return $this->headers;
	}

	/**
	 * レスポンスヘッダを設定
	 *
	 * @access public
	 * @param string $name フィールド名
	 * @param string $value フィールド値
	 */
	public function setHeader ($name, $value) {
		$this->headers->setParameter(
			StringUtils::stripControlCharacters($name),
			StringUtils::stripControlCharacters($value)
		);
	}

	/**
	 * バージョン番号込みのアプリケーション名を返す
	 *
	 * @access public
	 * @param string $lang 言語
	 * @return string アプリケーション名
	 */
	public function getName ($lang = 'ja') {
		return sprintf(
			'%s %s (Powered by %s %s)',
			(new ConstantHandler)['APP_NAME_' . $lang],
			BS_APP_VER,
			BS_CARROT_NAME,
			BS_CARROT_VER
		);
	}
}
