<?php
namespace Carrot3;

class UDPURL extends URL {
	protected function __construct ($contents) {
		parent::__construct($contents);
		$this['scheme'] = 'udp';
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
