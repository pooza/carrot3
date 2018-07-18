<?php
namespace Carrot3;

class MIMEType extends ParameterHolder {
	use Singleton;
	private $suffixes;
	private $aliases;
	const DEFAULT_TYPE = 'application/octet-stream';

	protected function __construct () {
		$this->suffixes = Tuple::create();
		$this->aliases = Tuple::create();
		foreach (ConfigManager::getInstance()->compile('mime') as $entry) {
			$entry = Tuple::create($entry);
			if ($entry['suffixes']) {
				foreach ($entry['suffixes'] as $suffix) {
					$this['.' . ltrim($suffix, '.')] = $entry['type'];
					if ($this->suffixes->hasParameter($entry['type'])) {
						$this->suffixes[] = '.' . ltrim($suffix, '.');
					} else {
						$this->suffixes[$entry['type']] = '.' . ltrim($suffix, '.');
					}
				}
			}
			if ($entry['alias_to']) {
				$this->aliases[$entry['type']] = $entry['alias_to'];
			}
		}
	}

	public function getParameter (?string $name) {
		return parent::getParameter('.' . ltrim(StringUtils::toLower($name), '.'));
	}

	public function setParameter (?string $name, $value) {
		if (!StringUtils::isBlank($value)) {
			$name = '.' . ltrim($name, '.');
			$name = StringUtils::toLower($name);
			parent::setParameter($name, $value);
		}
	}

	public function resolveType ($type) {
		if (StringUtils::isBlank($type)) {
			return MIMEType::DEFAULT_TYPE;
		}
		if ($alias = $this->aliases[$type]) {
			return $alias;
		}
		return $type;
	}

	public function getSuffixes () {
		return $this->suffixes;
	}

	public function getAllSuffixes () {
		$suffixes = Tuple::create();
		foreach ($this->params as $key => $value) {
			$suffixes[] = $key;
		}
		return $suffixes;
	}

	static public function getType ($suffix, $flags = MIMEUtils::IGNORE_INVALID_TYPE) {
		if ($type = self::getInstance()[MIMEUtils::getFileNameSuffix($suffix)]) {
			return $type;
		} else if ($flags & MIMEUtils::IGNORE_INVALID_TYPE) {
			return self::DEFAULT_TYPE;
		}
	}

	static public function getSuffix ($type):?string {
		return self::getInstance()->getSuffixes()[$type];
	}
}
