<?php
namespace Carrot3;

abstract class DocumentSet implements TextRenderer, HTTPRedirector, \IteratorAggregate {
	use HTTPRedirectorObject, BasicObject, KeyGenerator;
	protected $name;
	protected $error;
	protected $type;
	protected $cacheFile;
	protected $documents;
	protected $contents;
	protected $url;
	static protected $entries;

	public function __construct (string $name) {
		$this->name = $name;
		$this->documents = Tuple::create();

		if (($entry = $this->getEntries()[$name]) && ($files = $entry['files'])) {
			foreach ($files as $file) {
				$this->register($file);
			}
		} else {
			if (!StringUtils::isBlank($this->getPrefix())) {
				$this->register($this->getPrefix());
			}
			$this->register($name);
		}
		if (StringUtils::isBlank($this->getCacheFile()->getContents())) {
			$this->update();
		}
	}

	abstract protected function getDocumentClass ();

	abstract protected function getDirectoryName ();

	public function getSourceDirectory () {
		return FileUtils::getDirectory($this->getDirectoryName());
	}

	public function getCacheDirectory () {
		$parent = FileUtils::getDirectory($this->getDirectoryName() . '_cache');
		if (!$dir = $parent->getEntry($this->name)) {
			$dir = $parent->createDirectory($this->name);
		}
		$dir->setDefaultSuffix($parent->getDefaultSuffix());
		return $dir;
	}

	public function getCacheFile () {
		if (!$this->cacheFile) {
			$dir = $this->getCacheDirectory();
			if (!$this->cacheFile = $dir->getEntry($this->digest(), $this->getDocumentClass())) {
				$this->cacheFile = $dir->createEntry($this->digest(), $this->getDocumentClass());
			}
		}
		return $this->cacheFile;
	}

	protected function getConfigFiles ():Tuple {
		$files = Tuple::create();
		$prefix = mb_ereg_replace(
			StringUtils::toLower(__NAMESPACE__) . '\\\\_?',
			'',
			StringUtils::underscorize(Utils::getShortClass($this))
		);
		foreach (['application', 'carrot'] as $name) {
			if ($file = ConfigManager::getConfigFile($prefix . '/' . $name)) {
				$files[] = $file;
			}
		}
		return $files;
	}

	public function getName ():string {
		return $this->name;
	}

	public function getPrefix ():?string {
		$name = StringUtils::explode('.', $this->getName());
		if (1 < $name->count()) {
			return $name[0];
		}
		return null;
	}

	public function digest ():?string {
		$values = Tuple::create([$this->getName()]);
		foreach ($this as $entry) {
			$values[$entry->getPath()] = $entry->digest();
		}
		return $this->createKey($values);
	}

	public function register ($entry) {
		if (is_string($entry)) {
			$dir = $this->getSourceDirectory();
			if ($file = $dir->getEntry($entry, $this->getDocumentClass())) {
				$entry = $file;
			}
		}
		if ($entry instanceof Serializable) {
			$this->documents[] = $entry;
		}
		$this->digest = null;
		$this->cacheFile = null;
		$this->contents = null;
	}

	public function getContents ():string {
		return (string)$this->contents;
	}

	public function update () {
		$cache = $this->getCacheFile();
		$cache->getDirectory()->purge(Date::create());
		$contents = Tuple::create();
		foreach ($this as $file) {
			$file->serialize();
			$contents[] = $file->getSerialized()['minified'];
		}
		$cache->setContents($contents->join("\n"));
		LogManager::getInstance()->put($this . 'を更新しました。', $this);
		$this->contents = $cache->getContents();
	}

	protected function getEntries () {
		if (!self::$entries) {
			self::$entries = Tuple::create();
		}
		if (!self::$entries[Utils::getClass($this)]) {
			self::$entries[Utils::getClass($this)] = $entries = Tuple::create();
			foreach ($this->getSourceDirectory() as $file) {
				$entries[$file->getBaseName()] = Tuple::create();
			}
			foreach ($this->getConfigFiles() as $file) {
				foreach (ConfigManager::getInstance()->compile($file) as $key => $values) {
					$entries[$key] = Tuple::create($values);
				}
			}
			$entries->sort();
		}
		return self::$entries[Utils::getClass($this)];
	}

	public function getSize ():int {
		return strlen($this->getContents());
	}

	public function getType ():string {
		if (!$this->type) {
			$file = FileUtils::createTemporaryFile(null, $this->getDocumentClass());
			$this->type = $file->getType();
			$file->delete();
		}
		return $this->type;
	}

	public function getEncoding ():string {
		return 'utf-8';
	}

	public function validate ():bool {
		return StringUtils::isBlank($this->error);
	}

	public function getError ():?string {
		return $this->error;
	}

	public function getIterator () {
		return new Iterator($this->documents);
	}

	public function getURL ():?HTTPURL {
		if (!$this->url) {
			$this->url = FileUtils::createURL(
				$this->getDirectoryName() . '_cache',
				$this->getName() . '/' . $this->getCacheFile()->getName()
			);
		}
		return $this->url;
	}

	public function __toString () {
		return sprintf('%s "%s"', Utils::getClass($this), $this->getName());
	}
}
