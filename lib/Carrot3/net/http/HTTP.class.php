<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.http
 */

namespace Carrot3;

/**
 * HTTPプロトコル
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class HTTP extends Socket {

	/**
	 * HEADリクエスト
	 *
	 * @access public
	 * @param string $path パス
	 * @param iterable $params パラメータの配列
	 * @return HTTPResponse レスポンス
	 */
	public function sendHEAD ($path = '/', iterable $params = null) {
		$request = $this->createRequest();
		$request->setMethod('HEAD');
		$request->setURL($this->createRequestURL($path));
		if ($params) {
			$request->getURL()->setParameter($params);
		}
		return $this->send($request);
	}

	/**
	 * GETリクエスト
	 *
	 * @access public
	 * @param string $path パス
	 * @param iterable $params パラメータの配列
	 * @return HTTPResponse レスポンス
	 */
	public function sendGET ($path = '/', iterable $params = null) {
		$request = $this->createRequest();
		$request->setMethod('GET');
		$request->setURL($this->createRequestURL($path));
		if ($params) {
			$request->getURL()->setParameters($params);
		}
		return $this->send($request);
	}

	/**
	 * POSTリクエスト
	 *
	 * @access public
	 * @param string $path パス
	 * @param Renderer $renderer レンダラー
	 * @return HTTPResponse レスポンス
	 */
	public function sendPOST ($path = '/', Renderer $renderer = null) {
		$request = $this->createRequest();
		$request->setMethod('POST');
		$request->setRenderer($renderer);
		$request->setURL($this->createRequestURL($path));
		return $this->send($request);
	}

	/**
	 * パスからリクエストURLを生成して返す
	 *
	 * @access public
	 * @param string $href パス
	 * @return HTTPURL リクエストURL
	 */
	public function createRequestURL ($href) {
		$url = URL::create();
		$url['host'] = $this->getHost();
		$url['path'] = '/' . ltrim($href, '/');
		if ($this->isSSL()) {
			$url['scheme'] = 'https';
		} else {
			$url['scheme'] = 'http';
		}
		$url['port'] = $this->port;
		return $url;
	}

	protected function createRequest () {
		$request = new HTTPRequest;
		$request->setHeader('User-Agent', $this->controller->getName('en'));
		return $request;
	}

	protected function send (HTTPRequest $request) {
		if ($this->isOpened()) {
			throw new HTTPException($this . 'は既に開いています。');
		}
		$this->putLine($request->getContents());
		$response = new HTTPResponse;
		$response->setContents($this->getLines()->join("\n"));
		$response->setURL($request->getURL());
		$this->log($response);
		return $response;
	}

	/**
	 * ログを出力
	 *
	 * @access protected
	 * @param HTTPResponse $response レスポンス
	 */
	protected function log (HTTPResponse $response) {
		if (BS_DEBUG || !$response->validate()) {
			$message = new StringFormat('%s に "%s" を送信しました。 (%s)');
			$message[] = $this;
			$message[] = $response->getURL()->getFullPath();
			$message[] = self::getStatus($response->getStatus());
			LogManager::getInstance()->put($message, $this);
		}
		if (!$response->validate()) {
			$message = new StringFormat('%sからのレスポンスが不正です。 (%d %s)');
			$message[] = $this;
			$message[] = $response->getStatus();
			$message[] = $response->getError();
			$exception = new HTTPException($message);
			$exception->setResponse($response);
			throw $exception;
		}
	}

	/**
	 * SSLモードか？
	 *
	 * SSLはサポートしない。必要ならば、CurlHTTPを使用すること。
	 *
	 * @access public
	 * @return bool SSLモードならTrue
	 */
	public function isSSL () {
		return false;
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('HTTPソケット "%s"', $this->getName());
	}

	/**
	 * 規定のポートを返す
	 *
	 * @access public
	 * @return int port
	 */
	public function getDefaultPort () {
		return NetworkService::getPort('http');
	}

	/**
	 * 全てのステータスを返す
	 *
	 * @access public
	 * @return Tuple 全てのステータス
	 * @static
	 */
	static public function getAllStatus () {
		return Tuple::create(ConfigManager::getInstance()->compile('http_status'));
	}

	/**
	 * ステータスを返す
	 *
	 * @access public
	 * @param int $code ステータスコード
	 * @return string ステータス文字列
	 * @static
	 */
	static public function getStatus (int $code) {
		if ($status = self::getAllStatus()[$code]) {
			return $code . ' ' . $status['status'];
		}

		$message = new StringFormat('ステータスコード "%d" が正しくありません。');
		$message[] = $code;
		throw new HTTPException($message);
	}
}
