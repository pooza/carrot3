<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.http
 */

namespace Carrot3;

/**
 * httpリクエスト
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class HTTPRequest extends MIMEDocument {
	protected $method;
	protected $version = '1.0';
	protected $url;
	protected $useragent;

	/**
	 * httpバージョンを返す
	 *
	 * @access public
	 * @return string httpバージョン
	 */
	public function getVersion () {
		return $this->version;
	}

	/**
	 * メソッドを返す
	 *
	 * @access public
	 * @return string メソッド
	 */
	public function getMethod () {
		return $this->method;
	}

	/**
	 * メソッドを設定
	 *
	 * @access public
	 * @param string $method メソッド
	 */
	public function setMethod (string $method) {
		$this->method = StringUtils::toUpper($method);
		if (!self::isValidMethod($this->method)) {
			throw new HTTPException($this->method . 'は正しくないメソッドです。');
		}
	}

	/**
	 * レンダラーを設定
	 *
	 * @access public
	 * @param Renderer $renderer レンダラー
	 * @param int $flags フラグのビット列
	 *   MIMEUtils::WITHOUT_HEADER ヘッダを修正しない
	 *   MIMEUtils::WITH_HEADER ヘッダも修正
	 */
	public function setRenderer (Renderer $renderer, int $flags = MIMEUtils::WITH_HEADER) {
		$this->renderer = $renderer;
		if ($flags & MIMEUtils::WITH_HEADER) {
			$this->setHeader('Content-Type', $renderer);
		}
	}

	/**
	 * 送信先URLを返す
	 *
	 * @access public
	 * @return URL 送信先URL
	 */
	public function getURL ():?HTTPURL {
		return $this->url;
	}

	/**
	 * 送信先URLを設定
	 *
	 * @access public
	 * @param HTTPRedirector $url 送信先URL
	 */
	public function setURL (HTTPRedirector $url) {
		$this->url = $url->createURL();
		$this->setHeader('Host', $this->url['host']);
	}

	/**
	 * 出力内容を返す
	 *
	 * @access public
	 * @return string 出力内容
	 */
	public function getContents ():string {
		$this->setHeader('Content-Length', $this->getRenderer()->getSize());
		return $this->getRequestLine() . self::LINE_SEPARATOR . parent::getContents();
	}

	/**
	 * リクエスト行を返す
	 *
	 * @access public
	 * @return string 出力内容
	 */
	public function getRequestLine () {
		$line = new StringFormat('%s %s HTTP/%s');
		$line[] = $this->getMethod();
		$line[] = $this->getURL()->getFullPath();
		$line[] = $this->getVersion();
		return $line->getContents();
	}

	/**
	 * UserAgentを返す
	 *
	 * @access public
	 * @return UserAgent リモートホストのUserAgent
	 */
	public function getUserAgent ():UserAgent {
		if (!$this->useragent) {
			if ($header = $this->getHeader('user-agent')) {
				$this->setUserAgent($header->getEntity());
			} else {
				$this->setUserAgent(UserAgent::create(null, 'Default'));
			}
		}
		return $this->useragent;
	}

	/**
	 * UserAgentを設定
	 *
	 * @access public
	 * @param UserAgent $useragent リモートホストのUserAgent
	 */
	public function setUserAgent (UserAgent $useragent) {
		$this->useragent = $useragent;
	}

	/**
	 * 出力可能か？
	 *
	 * @access public
	 * @return bool 出力可能ならTrue
	 */
	public function validate ():bool {
		return $this->getMethod() && $this->getURL();
	}

	/**
	 * エラーメッセージを返す
	 *
	 * @access public
	 * @return string エラーメッセージ
	 */
	public function getError ():?string {
		return 'メソッド又は送信先URLが空欄です。';
	}

	/**
	 * サポートしているメソッドを返す
	 *
	 * @access public
	 * @return Tuple サポートしているメソッド
	 * @static
	 */
	static public function getMethods () {
		$methods = Tuple::create();
		$methods[] = 'HEAD';
		$methods[] = 'GET';
		$methods[] = 'POST';
		$methods[] = 'PUT';
		$methods[] = 'DELETE';
		return $methods;
	}

	/**
	 * サポートされたメソッドか？
	 *
	 * @access public
	 * @param string $method メソッド名
	 * @return bool サポートしているならTrue
	 * @static
	 */
	static public function isValidMethod ($method):bool {
		return self::getMethods()->isContain(StringUtils::toUpper($method));
	}
}
