<?php
namespace Carrot3;

class WWWFormRenderer extends ParameterHolder implements Renderer {
	protected $separator = '&';

	public function setParameter (?string $name, $value) {
		if (StringUtils::isBlank($value)) {
			$this->removeParameter($name);
		} else {
			parent::setParameter($name, $value);
		}
	}

	public function setParameters ($params) {
		if (!is_iterable($params)) {
			parse_str($params, $parsed);
			$params = Tuple::create($parsed);
		}
		parent::setParameters($params);
	}

	public function setSeparator ($separator) {
		$this->separator = $separator;
	}

	public function getContents ():string {
		return http_build_query($this->getParameters(), '', $this->separator);
	}

	public function digest ():?string {
		return Crypt::digest($this->getContents());
	}

	public function setContents ($contents) {
		$this->clear();
		$this->setParameters($contents);
	}

	public function getSize ():int {
		return strlen($this->getContents());
	}

	public function getType ():string {
		return 'application/x-www-form-urlencoded';
	}

	public function validate ():bool {
		return true;
	}

	public function getError ():?string {
		return null;
	}
}
