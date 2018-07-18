<?php
namespace Carrot3;

class LogDirectory extends Directory {
	public function __construct ($path = null) {
		if (!$path) {
			$path = FileUtils::getPath('log');
		}
		parent::__construct($path);
		$this->setDefaultSuffix('.log');
	}

	public function hasSubDirectory ():bool {
		return false;
	}

	public function getDefaultEntryClass ():string {
		return 'LogFile';
	}

	public function getSortOrder ():string {
		return self::SORT_DESC;
	}
}
