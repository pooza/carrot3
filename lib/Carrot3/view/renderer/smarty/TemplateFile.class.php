<?php
namespace Carrot3;

class TemplateFile extends File {
	private $engine;

	public function isBinary ():bool {
		return false;
	}

	public function setEngine (Smarty $engine) {
		$this->engine = $engine;
	}

	public function compile ():string {
		try {
			return $this->engine->fetch($this->getPath());
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
}
