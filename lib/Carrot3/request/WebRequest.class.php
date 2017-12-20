<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request
 */

namespace Carrot3;

/**
 * Webリクエスト
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class WebRequest extends Request {

	/**
	 * @access protected
	 */
	protected function __construct () {
		$this->setMethod($this->controller->getAttribute('REQUEST_METHOD'));
	}

	/**
	 * メソッドを設定
	 *
	 * @access public
	 * @param integer $method メソッド
	 */
	public function setMethod ($method) {
		parent::setMethod($method);
		switch ($this->getMethod()) {
			case 'GET':
			case 'HEAD':
				$this->setParameters($_GET);
				break;
			default:
				$this->setParameters($_GET);
				$this->setParameters($_POST);
				foreach ($_FILES as $key => $info) {
					if (!StringUtils::isBlank($info['name'])) {
						$info['is_file'] = true;
						$this[$key] = $info;
					}
				}
				break;
		}
	}

	/**
	 * アップロードされたファイルを返す
	 *
	 * @access public
	 * @param string $name POSTされた名前
	 * @param string $class クラス名
	 * @return string 出力内容
	 */
	public function getUploadedFile ($name = 'file', $class = 'File') {
		$class = $this->loader->getClass($class);
		return new $class($this[$name]['tmp_name']);
	}

	/**
	 * 出力内容を返す
	 *
	 * @access public
	 * @return string 出力内容
	 */
	public function getContents () {
		if (!$this->contents) {
			$contents = Tuple::create();
			$contents[] = $this->getRequestLine();
			foreach ($this->getHeaders() as $header) {
				$contents[] = $header->getName() . ': ' . $header->getContents();
			}
			$contents[] = null;
			$contents[] = $this->getBody();
			$this->contents = $contents->join(self::LINE_SEPARATOR);
		}
		return $this->contents;
	}

	/**
	 * httpバージョンを返す
	 *
	 * @access public
	 * @return string httpバージョン
	 */
	public function getVersion () {
		if (!$this->version) {
			$version = $this->controller->getAttribute('SERVER_PROTOCOL');
			$this->version = StringUtils::explode('/', $version)[1];
		}
		return $this->version;
	}

	/**
	 * レンダラーを返す
	 *
	 * @access public
	 * @return Renderer レンダラー
	 */
	public function getRenderer () {
		if (!extension_loaded('http')) {
			throw new HTTPException('httpモジュールがロードされていません。');
		}
		if (!$this->renderer) {
			$this->renderer = new RawRenderer;
			$this->renderer->setContents(http_get_request_body());
		}
		return $this->renderer;
	}

	/**
	 * ヘッダ一式を返す
	 *
	 * @access public
	 * @return string[] ヘッダ一式
	 */
	public function getHeaders () {
		if (!$this->headers) {
			$this->headers = Tuple::create();
			if (extension_loaded('http')) {
				$headers = http_get_request_headers();
			} else if (StringUtils::isContain('apache', PHP_SAPI)) {
				$headers = apache_request_headers();
			} else {
				$headers = [];
				foreach ($_SERVER as $key => $value) {
					if (mb_ereg('HTTP_(.*)', $key, $matches)) {
						$headers[str_replace('_', '-', $matches[1])] = $value;
					}
				}
			}
			foreach ($headers as $key => $value) {
				$this->setHeader($key, $value);
			}
		}
		return $this->headers;
	}

	/**
	 * 送信先URLを返す
	 *
	 * @access public
	 * @return URL 送信先URL
	 */
	public function getURL () {
		if (!$this->url) {
			$url = 'http';
			if ($this->isSSL()) {
				$url .= 's';
			}
			$url .= "://" . $this->controller->getHost()->getName();
			$this->url = URL::create($url);
			$this->url['path'] = $this->controller->getAttribute('REQUEST_URI');
		}
		return $this->url;
	}

	/**
	 * ケータイ環境か？
	 *
	 * @access public
	 * @return boolean ケータイ環境ならTrue
	 */
	public function isMobile () {
		return $this->getUserAgent()->isMobile();
	}

	/**
	 * スマートフォン環境か？
	 *
	 * @access public
	 * @return boolean スマートフォン環境ならTrue
	 */
	public function isSmartPhone () {
		return $this->getUserAgent()->isSmartPhone();
	}

	/**
	 * SSL環境か？
	 *
	 * @access public
	 * @return boolean SSL環境ならTrue
	 */
	public function isSSL () {
		return !!$this->controller->getAttribute('HTTPS');
	}

	/**
	 * 静的ファイルを返す
	 *
	 * @access public
	 * @return File リクエストされた静的ファイルがあれば返す
	 */
	public function getStaticFile () {
		$path = $this->getURL()['path'];
		if (mb_ereg('/$', $path)) {
			$path .= 'index.html';
		}
		return FileUtils::getDirectory('www')->getEntry($path);
	}

	/**
	 * Submitされたか？
	 *
	 * @access public
	 * @return boolean SubmitされたならTrue
	 */
	public function isSubmitted () {
		return !StringUtils::isBlank($this[FormElement::SUBMITTED_FIELD]);
	}
}
