<?php
namespace Carrot3;

class Directory extends DirectoryEntry implements \IteratorAggregate {
	private $suffix;
	private $entries;
	private $url;
	private $zip;
	const SORT_ASC = 'asc';
	const SORT_DESC = 'dsc';
	const WITHOUT_DOTTED = 1;
	const WITH_RECURSIVE = 2;

	public function __construct ($path) {
		$this->setPath($path);
		if (!is_dir($this->getPath())) {
			throw new FileException($this . 'を開くことができません。');
		}
	}

	protected function setPath ($path) {
		parent::setPath(rtrim($path, '/'));
	}

	public function getDefaultSuffix ():?string {
		return $this->suffix;
	}

	public function setDefaultSuffix ($suffix) {
		$this->suffix = ltrim($suffix, '*');
		$this->entries = null;
	}

	public function getEntryNames (int $flags = 0) {
		$names = Tuple::create();
		foreach ($this->getAllEntryNames() as $name) {
			if (($flags & self::WITHOUT_DOTTED) && FileUtils::isDottedName($name)) {
				continue;
			}
			if (fnmatch('*' . $this->getDefaultSuffix(), $name)) {
				$names[] = basename($name, $this->getDefaultSuffix());
			}
		}
		return $names;
	}

	public function getAllEntryNames () {
		if (!$this->entries) {
			$this->entries = Tuple::create();
			$iterator = new \DirectoryIterator($this->getPath());
			foreach ($iterator as $entry) {
				if (!$entry->isDot()) {
					$this->entries[] = $entry->getFilename();
				}
			}
			if ($this->getSortOrder() == self::SORT_DESC) {
				$this->entries->sort(Tuple::SORT_VALUE_DESC);
			} else {
				$this->entries->sort(Tuple::SORT_VALUE_ASC);
			}
		}
		return $this->entries;
	}

	public function clearEntryNames () {
		$this->entries = null;
	}

	public function getEntry (string $name, $class = null) {
		if (StringUtils::isBlank($class)) {
			$class = $this->getDefaultEntryClass();
		}
		$class = $this->loader->getClass($class);

		$path = $this->getPath() . '/' . StringUtils::stripControlCharacters($name);
		if ($this->hasSubDirectory() && is_dir($path)) {
			return new Directory($path);
		}
		foreach ([$path, $path . $this->getDefaultSuffix()] as $path) {
			if (is_file($path) || is_link($path)) {
				return new $class($path);
			}
		}
	}

	public function createEntry (string $name, $class = null) {
		if (StringUtils::isBlank($class)) {
			$class = $this->getDefaultEntryClass();
		}

		$name = basename($name, $this->getDefaultSuffix());
		$path = $this->getPath() . '/' . $name . $this->getDefaultSuffix();

		$class = $this->loader->getClass($class);
		$file = new $class($path);
		$file->setContents(null);
		$this->entries = null;
		return $file;
	}

	public function copyTo (Directory $dir):DirectoryEntry {
		$name = $this->getName();
		if ($dir->getPath() == $this->getDirectory()->getPath()) {
			while ($dir->getEntry($name)) {
				$name = StringUtils::increment($name);
			}
		}
		if (!$dest = $dir->getEntry($name)) {
			$dest = $dir->createDirectory($name);
		}
		foreach ($this as $entry) {
			$entry->copyTo($dest);
		}
		return $dest;
	}

	public function delete () {
		if ($this->isLink()) {
			if (!unlink($this->getPath())) {
				throw new FileException($this . 'を削除できません。');
			}
		} else {
			$this->clear();
			if (!rmdir($this->getPath())) {
				throw new FileException($this . 'を削除できません。');
			}
		}
	}

	public function clear () {
		$iterator = new \DirectoryIterator($this->getPath());
		foreach ($iterator as $entry) {
			if (FileUtils::isDottedName($entry->getFileName())) {
				continue;
			}
			$this->getEntry($entry->getFileName())->delete();
		}
	}

	public function clearDottedFiles () {
		foreach ($this as $entry) {
			$entry->clearDottedFiles();
		}
		parent::clearDottedFiles();
		$this->entries = null;
	}

	public function purge (Date $date = null) {
		if (!$date) {
			$date = Date::create()->setParameter('month', '-1');
		}
		foreach ($this as $entry) {
			if ($entry->isDotted()) {
				continue;
			}
			if ($entry->getUpdateDate()->isPast($date)) {
				$entry->delete();
			}
		}

		$message = new StringFormat('%s内の、%s以前のエントリーを削除しました。');
		$message[] = $this;
		$message[] = $date->format('Y/n/j');
		LogManager::getInstance()->put($message, $this);
	}

	public function createDirectory (string $name, $class = 'Carrot3\\Directory') {
		$path = $this->getPath() . '/' . $name;
		if (file_exists($path)) {
			if (!is_dir($path)) {
				throw new FileException($path . 'と同名のファイルが存在します。');
			}
		} else {
			mkdir($path);
		}
		$class = $this->loader->getClass($class);
		return new $class($path);
	}

	public function getURL ():?HTTPURL {
		if (!$this->url) {
			$documentRoot = FileUtils::getPath('www');
			if (mb_ereg('^' . $documentRoot, $this->getPath())) {
				$this->url = URL::create();
				$this->url['path'] = str_replace($documentRoot, '', $this->getPath()) . '/';
			}
		}
		return $this->url;
	}

	public function getArchive ($flags = self::WITHOUT_DOTTED) {
		if (!extension_loaded('zip')) {
			throw new FileException('zipモジュールがロードされていません。');
		}
		if (!$this->zip) {
			$this->zip = new ZipArchive;
			$this->zip->open(null, \ZipArchive::OVERWRITE);
			foreach ($this as $entry) {
				$this->zip->register($entry, null, $flags);
			}
			$this->zip->close();
		}
		return $this->zip;
	}

	public function setMode (int $mode, int $flags = 0) {
		parent::setMode($mode);
		if ($flags & self::WITH_RECURSIVE) {
			foreach ($this as $entry) {
				$entry->setMode($mode, $flags);
			}
		}
	}

	public function getIterator () {
		return new DirectoryIterator($this);
	}

	public function hasSubDirectory ():bool {
		return true;
	}

	public function getDefaultEntryClass () {
		return $this->loader->getClass('File');
	}

	public function getSortOrder () {
		return self::SORT_ASC;
	}

	public function __toString () {
		return sprintf('ディレクトリ "%s"', $this->getShortPath());
	}
}
