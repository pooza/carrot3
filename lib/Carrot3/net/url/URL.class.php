<?php
namespace Carrot3;

class URL implements \ArrayAccess, Assignable {
	use BasicObject;
	protected $attributes;
	protected $contents;
	protected $query;
	protected $fullpath;
	const PATTERN = '^[[:alnum:]]+:(//)?[[:graph:]]+$';

	protected function __construct ($contents = null) {
		$this->attributes = Tuple::create();
		$this->query = new WWWFormRenderer;
		$this->setContents($contents);
	}

	static public function create ($contents = null, $class = 'HTTP') {
		if (!$class = Loader::getInstance()->getClass($class . 'URL')) {
			throw new Exception('URLクラスが見つかりません。');
		}

		if (is_string($contents)) {
			$contents = parse_url($contents);
		}
		if (is_iterable($contents)) {
			$contents = Tuple::create($contents);
		} else {
			$contents = Tuple::create();
		}

		switch ($contents['scheme']) {
			case 'mailto':
			case 'tel':
			case 'javascript':
				return new self($contents);
			default:
				return new $class($contents);
		}
	}

	public function __clone () {
		$this->attributes = clone $this->attributes;
		$this->query = clone $this->query;
	}

	public function getContents ():string {
		if (!$this->contents) {
			if (StringUtils::isBlank($this->contents = $this->getHeadString())) {
				return null;
			}
			$this->contents .= $this->getFullPath();
		}
		return $this->contents;
	}

	public function setContents ($contents) {
		$this->attributes->clear();
		if (!is_iterable($contents)) {
			if (!mb_ereg(self::PATTERN, $contents)) {
				return false;
			}
			$contents = parse_url($contents);
		}

		foreach ($contents as $key => $value) {
			$this[$key] = $value;
		}
	}

	protected function getHeadString () {
		if (StringUtils::isBlank($this['scheme'])) {
			return null;
		}
		$head = $this['scheme'] . ':';

		if (!StringUtils::isBlank($this['user'])) {
			$head .= $this['user'];
			if (!StringUtils::isBlank($this['pass'])) {
				$head .= ':' . $this['pass'];
			}
			$head .= '@';
		}

		if ($this['host']) {
			$head .= '//' . $this['host']->getName();
			if ($this['port'] != NetworkService::getPort($this['scheme'])) {
				$head .= ':' . $this['port'];
			}
		}

		return $head;
	}

	public function getParameter (?string $name) {
		return $this->query[$name];
	}

	public function setParameter (?string $name, $value) {
		if (StringUtils::isBlank($value)) {
			return;
		}
		$this->query[$name] = $value;
		$this->contents = null;
	}

	public function getFullPath ():?string {
		return $this['path'];
	}

	public function getAttribute (string $name) {
		return $this->attributes[$name];
	}

	public function setAttribute (string $name, $value) {
		$this->contents = null;
		switch ($name) {
			case 'host':
				if (!($value instanceof Host)) {
					$value = new Host($value);
				}
				$this->attributes['host'] = $value;
				break;
			case 'query':
				$this->query->setContents($value);
				break;
			default:
				$this->attributes[$name] = $value;
				break;
		}
		return $this;
	}

	public function getAttributes ():Tuple {
		return $this->attributes;
	}

	public function validate ():bool {
		return !StringUtils::isBlank($this->getContents());
	}

	public function offsetExists ($key) {
		return $this->attributes->hasParameter($key);
	}

	public function offsetGet ($key) {
		return $this->getAttribute($key);
	}

	public function offsetSet ($key, $value) {
		$this->setAttribute($key, $value);
	}

	public function offsetUnset ($key) {
		$this->setAttribute($key, null);
	}

	public function assign () {
		return $this->getContents();
	}

	public function __toString () {
		return sprintf('URL "%s"', $this->getContents());
	}

	static public function encode ($value) {
		if (is_iterable($value)) {
			foreach ($value as $key => $item) {
				$value[$key] = self::encode($item);
			}
		} else {
			$value = urlencode($value);
		}
		return $value;
	}

	static public function rawencode ($value) {
		if (is_iterable($value)) {
			foreach ($value as $key => $item) {
				$value[$key] = self::encode($item);
			}
		} else {
			$value = rawurlencode($value);
		}
		return $value;
	}

	static public function decode ($value) {
		if (is_iterable($value)) {
			foreach ($value as $key => $item) {
				$value[$key] = self::decode($item);
			}
		} else {
			$value = urldecode($value);
		}
		return $value;
	}
}
