<?php
namespace Carrot3;

class HTTPResponse extends MIMEDocument {
	protected $version;
	protected $status;
	protected $message;
	protected $url;
	protected $method;
	const STATUS_PATTERN = '^HTTP/([[:digit:].]+) ([[:digit:]]{3}) (.*)$';

	protected function parseHeaders ($headers) {
		$this->getHeaders()->clear();
		$headers = StringUtils::convertLineSeparator($headers);
		foreach (StringUtils::explode("\n", $headers) as $line) {
			if (mb_ereg(self::STATUS_PATTERN, $line, $matches)) {
				$this->version = $matches[1];
				$this->status = (int)$matches[2];
				$this->message = $matches[3];
			} else if (mb_ereg('^([-[:alnum:]]+): *(.*)$', $line, $matches)) {
				$key = $matches[1];
				$this->setHeader($key, $matches[2]);
			} else if (mb_ereg('^[[:blank:]]+(.*)$', $line, $matches)) {
				$this->appendHeader($key, $matches[1]);
			}
		}
	}

	public function getVersion () {
		return $this->version;
	}

	public function getStatus ():int {
		if ($header = $this->getHeader('status')) {
			return (int)$header['code'];
		} else {
			return (int)$this->status;
		}
	}

	public function setStatus (int $code) {
		$this->status = $code;
		$this->setHeader('status', $code);
	}

	public function getURL ():?HTTPURL {
		return $this->url;
	}

	public function setURL (HTTPRedirector $url) {
		$this->url = $url->createURL();
	}

	public function getMethod ():?string {
		return $this->method;
	}

	public function setMethod (string $method) {
		$this->method = $method;
	}

	public function isHTML ():bool {
		return ($header = $this->getHeader('Content-Type')) && $header->isHTML();
	}

	public function validate ():bool {
		return ($this->getStatus() < 400);
	}

	public function getError ():?string {
		if (!$this->validate()) {
			return $this->message;
		}
	}
}
