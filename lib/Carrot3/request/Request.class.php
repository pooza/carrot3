<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request
 */

namespace Carrot3;

/**
 * 抽象リクエスト
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class Request extends HTTPRequest {
	use Singleton, BasicObject;
	protected $version = null;
	private $host;
	private $session;
	private $attributes;
	private $errors;

	/**
	 * シングルトンインスタンスを返す
	 *
	 * @access public
	 * @return Request インスタンス
	 * @static
	 */
	static public function getInstance () {
		if (!self::$instance) {
			if (PHP_SAPI == 'cli') {
				self::$instance = new ConsoleRequest;
			} else {
				self::$instance = new WebRequest;
			}
		}
		return self::$instance;
	}

	/**
	 * 全ての属性を削除
	 *
	 * @access public
	 */
	public function clearAttributes () {
		$this->getAttributes()->clear();
	}

	/**
	 * 属性を返す
	 *
	 * @access public
	 * @param string $name 属性名
	 * @return mixed 属性
	 */
	public function getAttribute (string $name) {
		return $this->getAttributes()[$name];
	}

	/**
	 * 属性値を全て返す
	 *
	 * @access public
	 * @return Tuple 属性値
	 */
	public function getAttributes ():Tuple {
		if (!$this->attributes) {
			$this->attributes = Tuple::create();
		}
		return $this->attributes;
	}

	/**
	 * コマンドラインパーサオプションを追加
	 *
	 * @access public
	 * @param string $name オプション名
	 */
	public function addOption (string $name) {
	}

	/**
	 * コマンドラインをパース
	 *
	 * @access public
	 */
	public function parse () {
	}

	/**
	 * エラーを全て返す
	 *
	 * @access public
	 * @return Tuple エラー
	 */
	public function getErrors () {
		if (!$this->errors) {
			$this->errors = Tuple::create();
		}
		return $this->errors;
	}

	/**
	 * 属性が存在するか？
	 *
	 * @access public
	 * @param string $name 属性名
	 * @return bool 存在すればTrue
	 */
	public function hasAttribute (string $name):bool {
		return $this->getAttributes()->hasParameter($name);
	}

	/**
	 * エラーが存在するか？
	 *
	 * @access public
	 * @param string $name エラー名
	 * @return bool 存在すればTrue
	 */
	public function hasError (string $name):bool {
		return $this->getErrors()->hasParameter($name);
	}

	/**
	 * ひとつ以上のエラーが存在するか？
	 *
	 * @access public
	 * @return bool 存在すればTrue
	 */
	public function hasErrors ():bool {
		return !!$this->getErrors()->count();
	}

	/**
	 * 属性を削除
	 *
	 * @access public
	 * @param string $name 属性名
	 */
	public function removeAttribute (string $name) {
		$this->getAttributes()->removeParameter($name);
	}

	/**
	 * エラーを削除
	 *
	 * @access public
	 * @param string $name エラー名
	 */
	public function removeError (string $name) {
		$this->getErrors()->removeParameter($name);
	}

	/**
	 * 属性を設定
	 *
	 * @access public
	 * @param string $name 属性名
	 * @param mixed $value 値
	 */
	public function setAttribute (string $name, $value) {
		$this->getAttributes()->setParameter((string)$name, $value);
	}

	/**
	 * 属性をまとめて設定
	 *
	 * @access public
	 * @param mixed $attributes 属性
	 */
	public function setAttributes ($attributes) {
		$this->getAttributes()->setParameters($attributes);
	}

	/**
	 * エラーを設定
	 *
	 * @access public
	 * @param string $name エラー名
	 * @param mixed $value 値
	 */
	public function setError (string $name, $value) {
		if ($value instanceof MessageContainer) {
			$value = $value->getMessage();
		}
		$this->getErrors()->setParameter($name, $value);
	}

	/**
	 * エラーをまとめて設定
	 *
	 * @access public
	 * @param mixed $errors エラー
	 */
	public function setErrors ($errors) {
		$this->getErrors()->setParameters($errors);
	}

	/**
	 * リモートホストを返す
	 *
	 * @access public
	 * @return string リモートホスト
	 */
	public function getHost () {
		if (!$this->host) {
			foreach (['X-FORWARDED-FOR', 'REMOTE_ADDR'] as $name) {
				if (!StringUtils::isBlank($hosts = $this->controller->getAttribute($name))) {
					try {
						$host = trim(StringUtils::explode(',', $hosts)->pop());
						return $this->host = new Host($host);
					} catch (\Exception $e) {
						return $this->host = new Host('0.0.0.0');
					}
				}
			}
		}
		return $this->host;
	}

	/**
	 * セッションハンドラを返す
	 *
	 * @access public
	 * @return SessionHandler セッションハンドラ
	 */
	public function getSession () {
		if (!$this->session) {
			$this->session = $this->getUserAgent()->createSession();
		}
		return $this->session;
	}

	/**
	 * セッションハンドラを生成する
	 *
	 * getSessionのエイリアス
	 *
	 * @access public
	 * @return SessionHandler セッションハンドラ
	 * @final
	 */
	final public function createSession () {
		return $this->getSession();
	}

	/**
	 * 実際のUserAgentを返す
	 *
	 * エミュレート環境でも、実際のUserAgentを返す。
	 *
	 * @access public
	 * @return UserAgent リモートホストのUserAgent
	 */
	public function getRealUserAgent () {
		if ($header = $this->getHeader('user-agent')) {
			return $header->getEntity();
		}
		return $this->getUserAgent();
	}

	/**
	 * 送信先URLを設定
	 *
	 * @access public
	 * @param HTTPRedirector $url 送信先URL
	 */
	public function setURL (HTTPRedirector $url) {
		throw new HTTPException(Utils::getClass($this) . 'のURLを設定できません。');
	}

	/**
	 * レンダラーを設定
	 *
	 * @access public
	 * @param Renderer $renderer レンダラー
	 * @param int $flags フラグのビット列
	 */
	public function setRenderer (Renderer $renderer, int $flags = 0) {
		throw new HTTPException(Utils::getClass($this) . 'はレンダラーを設定できません。');
	}

	/**
	 * ケータイ環境か？
	 *
	 * @access public
	 * @return bool ケータイ環境ならTrue
	 */
	public function isMobile ():bool {
		return false;
	}

	/**
	 * スマートフォン環境か？
	 *
	 * @access public
	 * @return bool スマートフォン環境ならTrue
	 */
	public function isSmartPhone ():bool {
		return false;
	}

	/**
	 * TLS環境か？
	 *
	 * @access public
	 * @return bool SSL環境ならTrue
	 */
	public function isTLS ():bool {
		return false;
	}

	/**
	 * TLS環境か？
	 *
	 * isTLSのエイリアス
	 *
	 * @access public
	 * @return bool SSL環境ならTrue
	 * @final
	 */
	final public function isSSL ():bool {
		return $this->isTLS();
	}
}
