<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.http.url
 */

namespace Carrot3;

/**
 * HTTPスキーマのURL
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class HTTPURL extends URL implements HTTPRedirector, ImageContainer {
	use HTTPRedirectorMethods;
	private $fullpath;
	private $useragent;
	private $query;
	private $shortURL;
	private $dirty = false;

	/**
	 * @access protected
	 * @param mixed $contents URL
	 */
	protected function __construct ($contents = null) {
		$this->attributes = Tuple::create();
		$this->query = new WWWFormRenderer;
		$this->setContents($contents);
	}

	/**
	 * @access public
	 */
	public function __clone () {
		$this->attributes = clone $this->attributes;
		$this->query = clone $this->query;
	}

	/**
	 * 属性を設定
	 *
	 * @access public
	 * @param string $name 属性の名前
	 * @param mixed $value 値
	 * @return HTTPURL 自分自身
	 */
	public function setAttribute (string $name, $value) {
		$this->contents = null;
		$this->fullpath = null;
		switch ($name) {
			case 'path':
				try {
					$values = Tuple::create(parse_url($value));
					$this->attributes['path'] = '/' . ltrim($values['path'], '/');
					$this->attributes['fragment'] = $values['fragment'];
					$this['query'] = $values['query'];
					$this->dirty = false;
				} catch (\Exception $e) {
					$this->attributes->clear();
					$this->attributes['path'] = $value;
					$this->dirty = true;
				}
				return $this;
			case 'query':
				$this->query->setContents($value);
				return $this;
			case 'fragment':
				$this->attributes[$name] = $value;
				return $this;
		}
		if (mb_ereg('^params?_(.*)$', $name, $matches)) {
			$this->setParameter($matches[1], $value);
			return $this;
		}
		return parent::setAttribute($name, $value);
	}

	/**
	 * URLを設定
	 *
	 * @access public
	 * @param mixed $contents URL
	 */
	public function setContents ($contents) {
		if (is_string($contents) || StringUtils::isBlank($contents)) {
			$contents = parse_url($contents);
		}
		if (is_iterable($contents)) {
			$contents = Tuple::create($contents);
		}
		if (StringUtils::isBlank($contents['scheme'])) {
			if ($this->request->isSSL()) {
				$contents['scheme'] = 'https';
			} else {
				$contents['scheme'] = 'http';
			}
		}
		if (StringUtils::isBlank($contents['host'])) {
			$contents['host'] = $this->controller->getHost();
		}
		parent::setContents($contents);
	}

	/**
	 * path以降を返す
	 *
	 * @access public
	 * @return string URLのpath以降
	 */
	public function getFullPath () {
		if (!$this->fullpath) {
			if (StringUtils::isBlank($this->attributes['path'])) {
				$this->fullpath = '/';
			} else {
				$this->fullpath = $this['path'];
			}
			if ($this->query->count()) {
				$this->fullpath .= '?' . $this->query->getContents();
			}
			if (!StringUtils::isBlank($this['fragment'])) {
				$this->fullpath .= '#' . $this['fragment'];
			}
		}
		return $this->fullpath;
	}

	/**
	 * パラメータを返す
	 *
	 * @access public
	 * @param string $name パラメータの名前
	 * @return string パラメータ
	 */
	public function getParameter (string $name) {
		return $this->query[$name];
	}

	/**
	 * パラメータを設定
	 *
	 * @access public
	 * @param string $name パラメータの名前
	 * @param string $value パラメータの値
	 */
	public function setParameter (string $name, $value) {
		if (StringUtils::isBlank($value)) {
			return;
		}
		$this->query[(string)$name] = $value;
		$this->fullpath = null;
		$this->contents = null;
	}

	/**
	 * クエリー文字列の全てのパラメータを返す
	 *
	 * @access public
	 * @return Tuple パラメータの配列
	 */
	public function getParameters () {
		return $this->query->getParameters();
	}

	/**
	 * パラメータを設定
	 *
	 * @access public
	 * @param mixed $params パラメータ文字列、又は配列
	 */
	public function setParameters ($params) {
		$this->query->setParameters($params);
		$this->fullpath = null;
	}

	/**
	 * 対象UserAgentを返す
	 *
	 * @access public
	 * @return UserAgent 対象UserAgent
	 */
	public function getUserAgent () {
		return $this->useragent;
	}

	/**
	 * 対象UserAgentを設定
	 *
	 * @access public
	 * @param UserAgent $useragent 対象UserAgent
	 */
	public function setUserAgent (UserAgent $useragent) {
		if ($this->useragent) {
			if ($this->useragent === $useragent) {
				return;
			}
			throw new UserAgentException('対象URLは設定済みです。');
		}

		$this->useragent = $useragent;
		if (!$this->isForeign()) {
			$this->setParameters($useragent->getQuery());
		}
		return $this;
	}

	/**
	 * Curlでフェッチして文字列で返す
	 *
	 * @access public
	 * @param string $class HTTPクラス名
	 * @return string フェッチした内容
	 */
	public function fetch ($class = 'CurlHTTP') {
		try {
			$class = $this->loader->getClass($class);
			$http = new $class($this['host']);
			$response = $http->sendGET($this->getFullPath());
			return $response->getRenderer()->getContents();
		} catch (\Exception $e) {
			throw new HTTPException($this . 'を取得できません。');
		}
	}

	/**
	 * favicon画像を返す
	 *
	 * @access public
	 * @return Image favicon画像
	 */
	public function getFavicon () {
		return $this->getImageFile('favicon');
	}

	/**
	 * キャッシュをクリア
	 *
	 * @access public
	 * @param string $size
	 */
	public function removeImageCache ($size) {
		if ($file = $this->getImageFile('image')) {
			$file->removeImageCache($size);
		}
	}

	/**
	 * 画像の情報を返す
	 *
	 * @access public
	 * @param string $size サイズ名
	 * @param int $pixel ピクセル数
	 * @param int $flags フラグのビット列
	 * @return Tuple 画像の情報
	 */
	public function getImageInfo ($size, ?int $pixel = null, int $flags = 0) {
		if ($file = $this->getImageFile($size)) {
			$info = (new ImageManager)->getInfo($file, $size, $pixel, $flags);
			$info['alt'] = $this->getID();
			return $info;
		}
	}

	/**
	 * 画像ファイルを返す
	 *
	 * @access public
	 * @param string $size サイズ名
	 * @return ImageFile 画像ファイル
	 */
	public function getImageFile ($size) {
		switch ($size) {
			case 'favicon':
				return (new GoogleFaviconsService)->getImageFile($this['host']);
			case 'qr':
				return (new GoogleChartService)->getQRCodeImageFile($this->getContents());
		}
	}

	/**
	 * 画像ファイルベース名を返す
	 *
	 * @access public
	 * @param string $size サイズ名
	 * @return string 画像ファイルベース名
	 */
	public function getImageFileBaseName ($size) {
		return $this->getID();
	}

	/**
	 * コンテナのIDを返す
	 *
	 * コンテナを一意に識別する値。
	 * ファイルならinode、DBレコードなら主キー。
	 *
	 * @access public
	 * @return int ID
	 */
	public function getID () {
		return Crypt::digest($this->getName());
	}

	/**
	 * コンテナの名前を返す
	 *
	 * @access public
	 * @return string 名前
	 */
	public function getName () {
		return $this->getContents();
	}

	/**
	 * コンテナのラベルを返す
	 *
	 * @access public
	 * @param string $language 言語
	 * @return string ラベル
	 */
	public function getLabel ($language = 'ja') {
		return $this->getID();
	}

	/**
	 * 外部のURLか？
	 *
	 * @access public
	 * @param mixed $host 対象ホスト
	 * @return bool 外部のURLならTrue
	 */
	public function isForeign ($host = null) {
		if ($host) {
			if ($host instanceof HTTPURL) {
				$host = $host['host'];
			} else if (!($host instanceof Host)) {
				$host = new Host($host);
			}
		} else {
			$host = $this->controller->getHost();
		}
		return $this['host']->isForeign($host);
	}

	/**
	 * 短縮URLを返す
	 *
	 * @access public
	 * @return URL 短縮URL
	 */
	public function getShortURL () {
		if (!$this->shortURL) {
			$service = $this->loader->createObject(BS_NET_URL_SHORTER . 'Service');
			if (!$service || !($service instanceof URLShorter)) {
				throw new HTTPException('URL短縮サービスが取得できません。');
			}
			$key = Crypt::digest([Utils::getClass($this), $this->getContents()]);
			$serials = new SerializeHandler;
			if ($url = $serials[$key]) {
				$this->shortURL = URL::create($url);
			} else {
				$this->shortURL = $service->getShortURL($this);
				$serials[$key] = $this->shortURL->getContents();
			}
		}
		return $this->shortURL;
	}

	/**
	 * 短縮URLを返す
	 *
	 * getShortURLのエイリアス
	 *
	 * @access public
	 * @return URL 短縮URL
	 * @final
	 */
	final public function getTinyURL () {
		return $this->getShortURL();
	}

	/**
	 * リダイレクト対象
	 *
	 * @access public
	 * @return URL
	 */
	public function getURL () {
		return $this;
	}

	/**
	 * リダイレクト
	 *
	 * @access public
	 * @return string ビュー名
	 */
	public function redirect () {
		$url = $this->createURL();
		$url->setParameters($this->request->getUserAgent()->getQuery());
		if (!$this->isForeign()) {
			$this->user->setAttribute('errors', $this->request->getErrors());
		}
		$this->controller->setHeader('Location', $url->getContents());
		return View::NONE;
	}
}
