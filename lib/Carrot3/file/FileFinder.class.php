<?php
namespace Carrot3;

class FileFinder {
	use BasicObject;
	private $directories;
	private $suffixes;
	private $pattern;
	private $class;

	public function __construct ($class = 'File') {
		$this->directories = Tuple::create();
		$this->suffixes = Tuple::create([null]);
		foreach ($this->controller->getSearchDirectories() as $dir) {
			$this->registerDirectory($dir);
		}
		$this->class = $this->loader->getClass($class);
	}

	public function execute ($file) {
		if ($file instanceof File) {
			return $this->execute($file->getPath());
		} else if (is_iterable($file)) {
			$params = Tuple::create($file);
			if (StringUtils::isBlank($params['src'])) {
				if ($record = (new RecordFinder($params))->execute()) {
					if ($attachment = $record->getAttachment($params['size'])) {
						return $this->execute($attachment);
					}
				}
			} else {
				return $this->execute($file['src']);
			}
		}

		if (Utils::isPathAbsolute($file)) {
			return new $this->class($file);
		}
		foreach ($this->directories as $dir) {
			foreach ($this->suffixes as $suffix) {
				if ($found = $dir->getEntry($file . $suffix, $this->class)) {
					return $found;
				}
			}
		}
	}

	public function registerDirectory ($dir) {
		if (is_string($dir)) {
			$dir = FileUtils::getDirectory($dir);
		}
		if ($dir instanceof Directory) {
			$this->directories->unshift($dir);
		}
	}

	public function clearDirectories () {
		$this->directories->clear();
	}

	public function registerSuffix ($suffix) {
		$this->suffixes->unshift('.' . ltrim($suffix, '.'));
	}

	public function registerSuffixes (ParameterHolder $suffixes) {
		foreach ($suffixes as $suffix) {
			$this->registerSuffix($suffix);
		}
		$this->suffixes->uniquize();
	}

	public function clearSuffixes () {
		$this->suffixes->clear();
		$this->suffixes[] = null;
	}
}
