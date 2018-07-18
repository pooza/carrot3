<?php
namespace Carrot3;

class ZipArchive extends \ZipArchive implements Renderer {
	private $file;
	private $temporaryFile;
	private $opened = false;

	public function __destruct () {
		if ($this->opened) {
			$this->close();
		}
	}

	public function open ($path = null, $flags = null) {
		if ($this->opened) {
			throw new FileException($this->getFile() . 'が開かれています。');
		}
		$this->setFile($path);
		$this->opened = true;
		return parent::open($this->getFile()->getPath(), self::OVERWRITE);
	}

	public function close () {
		if ($this->opened) {
			$this->opened = false;
			return parent::close();
		}
	}

	public function extractTo ($path, $entries = null) {
		if ($path instanceof Directory) {
			$path = $path->getPath() . '/';
		}
		$command = new CommandLine('bin/unzip');
		$command->setDirectory(FileUtils::getDirectory('unzip'));
		$command->push($this->getFile()->getPath());
		$command->push('-d');
		$command->push($path);
		$command->execute();
		return true;
	}

	public function register (DirectoryEntry $entry, $prefix = null, int $flags = 0) {
		if (($flags & Directory::WITHOUT_DOTTED) && $entry->isDotted()) {
			return;
		}

		if (StringUtils::isBlank($prefix)) {
			$path = $entry->getName();
		} else {
			$path = $prefix . '/' . $entry->getName();
		}
		if ($entry instanceof Directory) {
			$this->addEmptyDir($path);
			foreach ($entry as $node) {
				$this->register($node, $path, $flags);
			}
		} else {
			$this->addFile($entry->getPath(), $path);
		}
	}

	public function getFile () {
		if (!$this->file) {
			$this->temporaryFile = true;
			$this->file = FileUtils::createTemporaryFile('.zip');
		}
		return $this->file;
	}

	public function setFile ($file) {
		if ($this->opened) {
			throw new FileException($this->getFile() . 'が開かれています。');
		}
		if (StringUtils::isBlank($file)) {
			$file = null;
		} else if (!($file instanceof File)) {
			$path = $file;
			if (!Utils::isPathAbsolute($path)) {
				$path = FileUtils::getPath('tmp') . '/' . $path;
			}
			$this->temporaryFile = false;
			$file = new File($path);
		}
		$this->file = $file;
	}

	public function getContents ():string {
		$this->close();
		return $this->getFile()->getContents();
	}

	public function getSize ():int {
		return strlen($this->getContents());
	}

	public function getType ():string {
		return MIMEType::getType('zip');
	}

	public function validate ():bool {
		return true;
	}

	public function getError ():?string {
		return null;
	}
}
