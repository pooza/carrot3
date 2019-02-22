<?php
namespace Carrot3;

class PiconImage extends Image {
	protected $url;
	protected $service;
	protected $method;

	public function __construct (?iterable $params = null) {
		if ($params) {
			if ($params['file'] && ($params['file'] instanceof \Carrot3\ImageFile)) {
				$this->setImage($params['file']->getContents());
			}
			if ($params['url']) {
				$this->setURL(URL::create($params['url']));
			}
			if ($params['method']) {
				$this->setResizeMethod($params['method']);
			}
		}
	}

	public function setResizeMethod ($method) {
		$this->method = $method;
	}

	public function getWidth ():int {
		return getimagesizefromstring($this->getContents())[0];
	}

	public function getHeight ():int {
		return getimagesizefromstring($this->getContents())[1];
	}

	public function analyzeType ():string {
		return getimagesizefromstring($this->getContents())['mime'];
	}

	public function resize (int $width, int $height) {
		$this->getService()->resize($this, $width, $height);
	}

	public function resizeWidth (int $width) {
		if ($this->getWidth() < $width) {
			return;
		}
		$this->getService()->resizeWidth($this, $width, $this->method);
	}

	public function getURL ():?HTTPURL {
		return $this->url;
	}

	public function setURL (HTTPRedirector $url) {
		$this->url = $url->getURL();
	}

	protected function getService () {
		if (!$this->service && $this->url) {
			$this->service = new PiconService($this->url['host'], $this->url['port']);
		}
		return $this->service;
	}
}
