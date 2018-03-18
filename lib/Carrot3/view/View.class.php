<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view
 */

namespace Carrot3;

/**
 * 基底ビュー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class View extends HTTPResponse {
	use BasicObject;
	protected $nameSuffix;
	protected $action;
	protected $version = '1.0';
	const NONE = null;
	const ERROR = 'Error';
	const INPUT = 'Input';
	const SUCCESS = 'Success';

	/**
	 * @access public
	 * @param Action $action 呼び出し元アクション
	 * @param string $suffix ビュー名サフィックス
	 * @param Renderer $renderer レンダラー
	 */
	public function __construct (Action $action, $suffix, Renderer $renderer = null) {
		$this->action = $action;
		$this->nameSuffix = $suffix;

		if (!$renderer) {
			$renderer = $this->createDefaultRenderer();
		}
		$this->setRenderer($renderer);

		$this->setHeader('X-Frame-Options', BS_VIEW_FRAME_OPTIONS);
		$this->setHeader('X-Content-Type-Options', BS_VIEW_CONTENT_TYPE_OPTIONS);
		$this->setHeader('X-UA-Compatible', BS_VIEW_UA_COMPATIBLE);
		$this->setHeader('X-XSS-Protection', BS_VIEW_XSS_PROTECTION);
	}

	/**
	 * @access public
	 * @param string $method メソッド名
	 * @param mixed $values 引数
	 */
	public function __call ($method, $values) {
		return Utils::executeMethod($this->renderer, $method, $values);
	}

	/**
	 * 初期化
	 *
	 * @access public
	 * @return bool 初期化が成功すればTrue
	 */
	public function initialize () {
		if ($filename = $this->request->getAttribute('filename')) {
			$this->setFileName($filename);
		}
		return true;
	}

	/**
	 * 実行
	 *
	 * @access public
	 */
	public function execute () {
	}

	/**
	 * ビュー名を返す
	 *
	 * @access public
	 * @return string ビュー名
	 */
	public function getName () {
		return $this->getAction()->getName() . $this->getNameSuffix();
	}

	/**
	 * ビュー名のサフィックスを返す
	 *
	 * @access public
	 * @return string ビュー名のサフィックス
	 */
	public function getNameSuffix () {
		return $this->nameSuffix;
	}

	/**
	 * 規定のレンダラーを生成して返す
	 *
	 * @access protected
	 * @return Renderer レンダラー
	 */
	protected function createDefaultRenderer () {
		return new RawRenderer;
	}

	/**
	 * レンダリング
	 *
	 * @access public
	 */
	public function render () {
		if (!$this->renderer->validate()) {
			if (!$message = $this->renderer->getError()) {
				$message = 'レンダラーに登録された情報が正しくありません。';
			}
			throw new ViewException($message);
		}

		$this->setHeader('content-type', MIMEUtils::getContentType($this->renderer));

		$this->putHeaders();
		mb_http_output('pass');
		echo $this->renderer->getContents();
	}

	/**
	 * モジュールを返す
	 *
	 * @access public
	 * @return Module モジュール
	 */
	public function getModule () {
		return $this->getAction()->getModule();
	}

	/**
	 * アクションを返す
	 *
	 * @access public
	 * @return Action アクション
	 */
	public function getAction () {
		return $this->action;
	}

	/**
	 * レスポンスヘッダを送信
	 *
	 * @access public
	 */
	public function putHeaders () {
		foreach ($this->controller->getHeaders() as $key => $value) {
			$this->setHeader($key, $value);
		}

		$this->setCacheControl($this->isCacheable());

		if ($header = $this->getHeader('status')) {
			self::putHeader('HTTP/' . $this->getVersion() . ' ' . $header->getContents());
		}
		foreach ($this->getHeaders() as $name => $header) {
			self::putHeader($header->format(MIMEHeader::WITHOUT_CRLF));
		}
	}

	/**
	 * HTTPキャッシュ有効か
	 *
	 * @access public
	 * @return bool 有効ならTrue
	 */
	public function isCacheable () {
		return false;
	}

	/**
	 * キャッシュ制御を設定
	 *
	 * @access public
	 * @param bool $mode キャッシュONならTrue
	 */
	public function setCacheControl (bool $mode) {
		$expires = Date::create();
		if (!!$mode) {
			$value = new StringFormat('%s, max-age=%d');
			$value[] = BS_APP_HTTP_CACHE_MODE;
			$value[] = BS_APP_HTTP_CACHE_SECONDS;
			$this->setHeader('Cache-Control', $value->getContents());
			$this->setHeader('Pragma', BS_APP_HTTP_CACHE_MODE);
			$expires['second'] = '+' . BS_APP_HTTP_CACHE_SECONDS;
		} else {
			$this->setHeader('Cache-Control', 'no-cache, must-revalidate');
			$this->setHeader('Pragma', 'no-cache');
			$expires = Date::create();
			$expires['hour'] = '-1';
		}
		$this->setHeader('Expires', $expires->format(\DateTime::RFC1123));
	}

	/**
	 * ファイル名を設定
	 *
	 * @access public
	 * @param string $filename ファイル名
	 * @param string $mode モード
	 */
	public function setFileName (string $name, $mode = MIMEUtils::ATTACHMENT) {
		parent::setFileName($this->useragent->encodeFileName($name), $mode);
		$this->filename = $name;
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('%sのビュー "%s"', $this->getModule(), $this->getName());
	}

	/**
	 * 全てのサフィックスを返す
	 *
	 * @access public
	 * @return Tuple 全てのサフィックス
	 */
	static public function getNameSuffixes () {
		return Tuple::create([
			self::ERROR,
			self::INPUT,
			self::SUCCESS,
		]);
	}

	/**
	 * ヘッダを送信
	 *
	 * @access public
	 * @param string $header ヘッダ
	 * @static
	 */
	static public function putHeader ($header) {
		if (Request::getInstance() instanceof ConsoleRequest) {
			return;
		}
		if (headers_sent()) {
			throw new ViewException('レスポンスヘッダを送信できません。');
		}
		header($header);
	}
}
