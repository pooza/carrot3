<?php
namespace Carrot3;

class DirectoryIterator extends Iterator {
	private $directory;

	public function __construct (Directory $directory, int $flags = 0) {
		$this->directory = $directory;
		parent::__construct($directory->getEntryNames($flags));
	}

	public function current () {
		if ($name = parent::current()) {
			return $this->directory->getEntry($name);
		}
	}
}
