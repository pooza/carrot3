<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.url
 */

namespace Carrot3;

/**
 * HTTPスキーマのURL
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class HTTPURL extends URL implements HTTPRedirector, ImageContainer {
	use HTTPRedirectorObject, KeyGenerator;
	private $fullpath;
	private $useragent;
	private $shortURL;
	private $dirty = false;

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
			case 'scheme':
				$this->attributes['scheme'] = $value;
				$this->attributes['port'] = NetworkService::getPort($value);
				break;
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
			if ($this->request->isTLS()) {
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
	 * パラメータを設定
	 *
	 * @access public
	 * @param string $name パラメータの名前
	 * @param string $value パラメータの値
	 */
	public function setParameter (?string $name, $value) {
		parent::setParameter($name, $value);
		$this->fullpath = null;
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
	public function getUserAgent ():UserAgent {
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
			$response = (new $class($this['host']))->sendGET($this->getFullPath());
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
	public function removeImageCache (string $size) {
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
	public function getImageInfo (string $size, ?int $pixel = null, int $flags = 0) {
		if ($file = $this->getImageFile($size)) {
			$info = (new ImageManager)->getInfo($file, $size, $pixel, $flags);
			$info['alt'] = $this['host']->getName();
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
	public function getImageFile (string $size):?ImageFile {
		switch ($size) {
			case 'favicon':
				return (new GoogleFaviconsService)->getImageFile($this['host']);
			case 'qr':
				return (new GoogleChartService)->getQRCodeImageFile($this->getContents());
		}
	}

	/**
	 * コンテナの名前を返す
	 *
	 * @access public
	 * @return string 名前
	 */
	public function getName ():?string {
		return $this->getContents();
	}

	/**
	 * コンテナのラベルを返す
	 *
	 * @access public
	 * @param string $lang 言語
	 * @return string ラベル
	 */
	public function getLabel (?string $lang = 'ja'):?string {
		return $this->getID();
	}

	/**
	 * 外部のURLか？
	 *
	 * @access public
	 * @param mixed $host 対象ホスト
	 * @return bool 外部のURLならTrue
	 */
	public function isForeign ($host = null):bool {
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
			$key = $this->createKey([$this->getContents()]);
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
	public function getURL ():?HTTPURL {
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
