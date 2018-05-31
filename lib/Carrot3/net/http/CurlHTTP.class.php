<?php
namespace Carrot3;

class CurlHTTP extends HTTP {
	protected $engine;
	protected $uid;
	protected $password;
	protected $tls = false;

	public function __construct ($host, int $port = null, $protocol = NetworkService::TCP) {
		parent::__construct($host, $port, $protocol);
		if ($port == NetworkService::getPort('https')) {
			$this->setSSL(true);
		}
	}

	public function sendHEAD ($path = '/', iterable $params = null) {
		$this->setAttribute('nobody', true);
		return parent::sendHEAD($path, $params);
	}

	public function sendGET ($path = '/', iterable $params = null) {
		$this->setAttribute('httpget', true);
		return parent::sendGET($path, $params);
	}

	public function sendPOST ($path = '/', Renderer $renderer = null, File $file = null) {
		$request = $this->createRequest();
		$request->setMethod('POST');
		$request->setRenderer($renderer);
		$request->setURL($this->createRequestURL($path));
		$this->setAttribute('post', true);
		if ($file && ($renderer instanceof ParameterHolder)) {
			$params = $renderer->getParameters();
			$params['file'] = new \CURLFile($file->getPath());
			$request->setHeader('Content-Type', 'multipart/form-data');
			$this->setAttribute('safe_upload', true);
			$this->setAttribute('postfields', $params);
		} else {
			$this->setAttribute('postfields', $request->getRenderer()->getContents());
		}
		return $this->send($request);
	}

	protected function send (HTTPRequest $request) {
		$headers = [];
		foreach ($request->getHeaders() as $header) {
			$headers[] = $header->getName() . ': ' . $header->getContents();
		}
		$this->setAttribute('httpheader', $headers);
		$this->setAttribute('url', $request->getURL()->getContents());
		$this->setAttribute('port', $this->port);

		$response = new HTTPResponse;
		$response->setURL($request->getURL());
		$response->setMethod($request->getMethod());
		if (($contents = curl_exec($this->getCurl())) === false) {
			throw new HTTPException($request->getURL() . 'へ送信できません。');
		}
		$response->setContents($this->trimResponse($contents));
		$this->log($response);
		return $response;
	}

	protected function trimResponse ($contents) {
		foreach ([MIMEDocument::LINE_SEPARATOR, "\n"] as $separator) {
			$delimiter = $separator . $separator;
			$parts = StringUtils::explode($delimiter, $contents);
			if (1 < $parts->count()) {
				foreach ($parts as $index => $part) {
					if (mb_eregi('^HTTP/[[:digit:]]+.[[:digit:]]+ (100|301|302)', $part)) {
						$parts->removeParameter($index);
					}
				}
				return $parts->join($delimiter);
			}
		}
	}

	protected function getCurl () {
		if (!$this->engine) {
			if (!extension_loaded('curl')) {
				throw new HTTPException('curlモジュールがロードされていません。');
			}

			$this->engine = curl_init();
			$this->setAttribute('autoreferer', true);
			$this->setAttribute('useragent', $this->controller->getName('en'));
			$this->setAttribute('followlocation', true);
			$this->setAttribute('header', true);
			$this->setAttribute('returntransfer', true);
			$this->setAttribute('maxredirs', 32);
			$this->setAttribute('ssl_verifypeer', false);
		}
		return $this->engine;
	}

	public function setAttribute (string $name, $value) {
		if (!$this->getCurl()) {
			return;
		}
		foreach (['CURLOPT', 'CURL', ''] as $prefix) {
			$constants = new ConstantHandler($prefix);
			if ($constants->hasParameter($name)) {
				curl_setopt($this->getCurl(), $constants[$name], $value);
				return;
			}
		}
	}

	public function setAuth ($uid, $password) {
		if (StringUtils::isBlank($password)) {
			return;
		}
		$this->uid = $uid;
		$this->password = Crypt::getInstance()->decrypt($password);
		$this->setAttribute('userpwd', $this->uid . ':' . $this->password);
	}

	public function isTLS ():bool {
		return $this->tls;
	}

	public function setSSL (bool $mode) {
		$this->tls = !!$mode;
		$this->name = null;
	}
}
