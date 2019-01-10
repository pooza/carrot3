<?php
namespace Carrot3;

class HTTPURL extends URL implements HTTPRedirector, ImageContainer {
	use HTTPRedirectorObject, KeyGenerator;
	private $useragent;
	private $shortURL;
	private $dirty = false;

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
				} catch (\Throwable $e) {
					$this->attributes->clear();
					$this->attributes['path'] = $value;
					$this->dirty = true;
				}
				return $this;
		}
		return parent::setAttribute($name, $value);
	}

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

	public function getFullPath ():?string {
		if (!$this->fullpath) {
			if (StringUtils::isBlank($this->attributes['path'])) {
				$this->fullpath = '/';
			} else {
				$this->fullpath = $this->attributes['path'];
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

	public function setParameter (?string $name, $value) {
		parent::setParameter($name, $value);
		$this->fullpath = null;
	}

	public function getParameters () {
		return $this->query->getParameters();
	}

	public function setParameters ($params) {
		$this->query->setParameters($params);
		$this->fullpath = null;
	}

	public function getUserAgent ():UserAgent {
		return $this->useragent;
	}

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

	public function fetch ($class = 'CurlHTTP') {
		try {
			$class = $this->loader->getClass($class);
			$response = (new $class($this['host']))->sendGET($this->getFullPath());
			return $response->getRenderer()->getContents();
		} catch (\Throwable $e) {
			throw new HTTPException($this . 'を取得できません。');
		}
	}

	public function getFavicon () {
		return $this->getImageFile('favicon');
	}

	public function removeImageCache (string $size) {
		if ($file = $this->getImageFile('image')) {
			$file->removeImageCache($size);
		}
	}

	public function getImageInfo (string $size, ?int $pixel = null, int $flags = 0) {
		if ($file = $this->getImageFile($size)) {
			$info = (new ImageManager)->getInfo($file, $size, $pixel, $flags);
			$info['alt'] = $this['host']->getName();
			return $info;
		}
	}

	public function getImageFile (string $size):?ImageFile {
		switch ($size) {
			case 'favicon':
				return (new GoogleFaviconsService)->getImageFile($this['host']);
			case 'qr':
				return (new GoogleChartService)->getQRCodeImageFile($this->getContents());
		}
	}

	public function getName ():?string {
		return $this->getContents();
	}

	public function getLabel (?string $lang = 'ja'):?string {
		return $this->getID();
	}

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

	final public function getTinyURL () {
		return $this->getShortURL();
	}

	public function getURL ():?HTTPURL {
		return $this;
	}

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
