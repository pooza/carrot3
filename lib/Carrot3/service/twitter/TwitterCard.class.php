<?php
namespace Carrot3;

class TwitterCard extends ParameterHolder {
	protected $body;

	public function __construct (?string $body = null) {
		$this->setBody($body);
		$this['card'] = 'summary';
	}

	public function setBody (?string $body = null) {
		if (!StringUtils::isBlank($body)) {
			$this->body = $body;
			if (mb_ereg('\\<img.*?src="(.*?)".*?\\>', $body, $matches)) {
				$url = URL::create($matches[1]);
				$this['image'] = $url->getContents();
			}
		}
	}
}
