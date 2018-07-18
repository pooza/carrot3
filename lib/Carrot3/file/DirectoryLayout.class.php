<?php
namespace Carrot3;

class DirectoryLayout extends ParameterHolder {
	use Singleton, BasicObject;
	private $config;

	protected function __construct () {
		$this->config = Tuple::create();
		$entries = Tuple::create();
		$entries[] = 'carrot';
		$entries[] = 'application';
		$entries[] = $this->controller->getHost()->getName();
		foreach ($entries as $entry) {
			if ($file = ConfigManager::getConfigFile('layout/' . $entry)) {
				foreach (ConfigManager::getInstance()->compile($file) as $key => $values) {
					$this->config[$key] = Tuple::create($values);
				}
			}
		}
	}

	private function getEntry (string $name) {
		if (!$info = $this->config[$name]) {
			$message = new StringFormat('ディレクトリ "%s" が見つかりません。');
			$message[] = $name;
			throw new FileException($message);
		}
		return $info;
	}

	public function getEntries () {
		return $this->config;
	}

	public function getParameter (?string $name) {
		if (!$this->hasParameter($name) && ($info = $this->getEntry($name))) {
			if (!!$info['constant']) {
				$dir = new Directory((new ConstantHandler)[$name . '_DIR']);
			} else if (!!$info['platform']) {
				$dir = $this->controller->getPlatform()->getDirectory($name);
			} else if (!StringUtils::isBlank($info['name'])) {
				$dir = $this[$info['parent']]->getEntry($info['name']);
			} else {
				$dir = $this[$info['parent']]->getEntry($name);
			}

			if ($dir instanceof Directory) {
				if (!StringUtils::isBlank($info['class'])) {
					$class = $this->loader->getClass($info['class']);
					$dir = new $class($dir->getPath());
				}
				if (!StringUtils::isBlank($info['suffix'])) {
					$dir->setDefaultSuffix($info['suffix']);
				}
				$this->params[$name] = $dir;
			}
		}
		return $this->params[$name];
	}

	public function createURL (string $name):?HTTPURL {
		if (($info = $this->getEntry($name)) && StringUtils::isBlank($info['url'])) {
			if (StringUtils::isBlank($info['href'])) {
				$info['url'] = $this[$name]->getURL();
			} else {
				$info['url'] = URL::create();
				$info['url']['path'] = $info['href'];
			}
		}
		return clone $info['url'];
	}
}
