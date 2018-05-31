<?php
namespace Carrot3;

class HTTP extends Socket {
	public function sendHEAD ($path = '/', iterable $params = null) {
		$request = $this->createRequest();
		$request->setMethod('HEAD');
		$request->setURL($this->createRequestURL($path));
		if ($params) {
			$request->getURL()->setParameter($params);
		}
		return $this->send($request);
	}

	public function sendGET ($path = '/', iterable $params = null) {
		$request = $this->createRequest();
		$request->setMethod('GET');
		$request->setURL($this->createRequestURL($path));
		if ($params) {
			$request->getURL()->setParameters($params);
		}
		return $this->send($request);
	}

	public function sendPOST ($path = '/', Renderer $renderer = null) {
		$request = $this->createRequest();
		$request->setMethod('POST');
		$request->setRenderer($renderer);
		$request->setURL($this->createRequestURL($path));
		return $this->send($request);
	}

	public function createRequestURL ($href) {
		$url = URL::create();
		$url['host'] = $this->getHost();
		$url['path'] = '/' . ltrim($href, '/');
		if ($this->isTLS()) {
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
		$response->setMethod($request->getMethod());
		$this->log($response);
		return $response;
	}

	protected function log (HTTPResponse $response) {
		if (BS_DEBUG || !$response->validate()) {
			$message = new StringFormat('%s に "%s %s" を送信しました。 (%s)');
			$message[] = $this;
			$message[] = $response->getMethod();
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

	public function isTLS ():bool {
		return false;
	}

	public function getDefaultPort () {
		return NetworkService::getPort('http');
	}

	public function __toString () {
		return sprintf('HTTPソケット "%s"', $this->getName());
	}

	static public function getAllStatus () {
		return Tuple::create(ConfigManager::getInstance()->compile('http_status'));
	}

	static public function getStatus (int $code) {
		if ($status = self::getAllStatus()[$code]) {
			return $code . ' ' . $status['status'];
		}

		$message = new StringFormat('ステータスコード "%d" が正しくありません。');
		$message[] = $code;
		throw new HTTPException($message);
	}
}
