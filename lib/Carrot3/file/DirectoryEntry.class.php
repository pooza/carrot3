<?php
namespace Carrot3;

abstract class DirectoryEntry {
	use BasicObject;
	protected $path;
	protected $id;
	private $suffix;
	private $shortPath;
	private $linkTarget;
	protected $directory;

	public function getID () {
		if (!$this->id) {
			$this->id = Crypt::digest([
				$this->getPath(),
				fileinode($this->getPath()),
			]);
		}
		return $this->id;
	}

	public function getName ():string {
		return basename($this->getPath());
	}

	final public function setName (string $name) {
		return $this->rename($name);
	}

	public function rename (string $name) {
		if (!$this->isExists()) {
			throw new FileException($this . 'が存在しません。');
		} else if (StringUtils::isContain('/', $name)) {
			throw new FileException($this . 'をリネームできません。');
		}

		$path = $this->getDirectory()->getPath() . '/' . basename($name);

		$command = new CommandLine('mv');
		$command->setStderrRedirectable();
		$command->push($this->getPath());
		$command->push($path);
		if ($command->getReturnCode()) {
			throw new FileException($command->getResult());
		}

		$this->setPath($path);
		$this->getDirectory()->clearEntryNames();
	}

	abstract public function delete ();

	public function getPath () {
		return $this->path;
	}

	protected function setPath ($path) {
		if (!Utils::isPathAbsolute($path) || StringUtils::isContain('../', $path)) {
			$message = new StringFormat('パス"%s"が正しくありません。');
			$message[] = $path;
			throw new FileException($message);
		}
		$this->path = $path;
		$this->suffix = null;
	}

	public function getShortPath ():string {
		if (!$this->shortPath) {
			$this->shortPath = str_replace(
				FileUtils::getPath('root') . '/',
				'',
				$this->getPath()
			);
		}
		return $this->shortPath;
	}

	public function moveTo (Directory $dir) {
		if (!$this->isExists()) {
			throw new FileException($this . 'が存在しません。');
		}

		$path = $dir->getPath() . '/' . $this->getName();
		$command = new CommandLine('mv');
		$command->setStderrRedirectable();
		$command->push($this->getPath());
		$command->push($path);
		if ($command->getReturnCode()) {
			throw new FileException($command->getResult());
		}

		$this->setPath($path);
	}

	public function copyTo (Directory $dir):DirectoryEntry {
		$path = $dir->getPath() . '/' . $this->getName();
		if (!copy($this->getPath(), $path)) {
			throw new FileException($this . 'をコピーできません。');
		}
		$class = Utils::getClass($this);
		return new $class($path);
	}

	public function clearDottedFiles () {
		if ($this->isDotted()) {
			$this->delete();
		}
	}

	public function getSuffix ():string {
		if (!$this->suffix) {
			$this->suffix = FileUtils::getSuffix($this->getName());
		}
		return $this->suffix;
	}

	public function getBaseName ():string {
		return basename($this->getPath(), $this->getSuffix());
	}

	public function isDotted ():bool {
		return FileUtils::isDottedName($this->getName());
	}

	public function isLink ():bool {
		return is_link($this->getPath());
	}

	public function getLinkTarget ():DirectoryEntry {
		if ($this->isLink() && !$this->linkTarget) {
			$class = Utils::getClass($this);
			$this->linkTarget = new $class(readlink($this->getPath()));
		}
		return $this->linkTarget;
	}

	public function createLink (Directory $dir, string $name = null):DirectoryEntry {
		if (StringUtils::isBlank($name)) {
			$name = $this->getName();
		}

		$path = $dir->getPath() . '/' . $name;
		if (is_link($path)) {
			unlink($path);
		}
		symlink($this->getPath(), $path);
		return $dir->getEntry($name);
	}

	public function getDirectory ():Directory {
		if (!$this->directory) {
			$this->directory = new Directory(dirname($this->getPath()));
		}
		return $this->directory;
	}

	public function getCreateDate ():?Date {
		if ($this->isExists()) {
			return Date::create(filectime($this->getPath()), Date::TIMESTAMP);
		}
	}

	public function getUpdateDate ():?Date {
		if ($this->isExists()) {
			return Date::create(filemtime($this->getPath()), Date::TIMESTAMP);
		}
		return null;
	}

	public function isExists ():bool {
		return file_exists($this->getPath());
	}

	public function isReadable ():bool {
		return is_readable($this->getPath());
	}

	public function isWritable ():bool {
		return is_writable($this->getPath());
	}

	public function setMode (int $mode, int $flags = 0) {
		if (!chmod($this->getPath(), $mode)) {
			throw new FileException($this . 'のファイルモードを変更できません。');
		}
	}
}
