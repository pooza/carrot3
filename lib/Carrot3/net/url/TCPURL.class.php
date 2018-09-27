<?php
namespace Carrot3;

class TCPURL extends URL {
	protected function __construct ($contents) {
		parent::__construct($contents);
		$this['scheme'] = 'tcp';
	}

	public function getFullPath ():?string {
		if (!$this->fullpath) {
			if (StringUtils::isBlank($this->attributes['path'])) {
				$this->fullpath = '/';
			} else {
				$this->fullpath = $this['path'];
			}
			if ($this->query->count()) {
				$this->fullpath .= '?' . $this->query->getContents();
			}
		}
		return $this->fullpath;
	}
}
