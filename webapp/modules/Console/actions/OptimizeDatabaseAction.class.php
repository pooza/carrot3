<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage Console
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

namespace Carrot3\ConsoleModule;
use \Carrot3 as C;

class OptimizeDatabaseAction extends C\Action {
	private $database;

	private function getDatabase () {
		if (!$this->database) {
			if (!$name = $this->request['d']) {
				$name = 'default';
			}
			$this->database = C\Database::getInstance($name);
		}
		return $this->database;
	}

	public function initialize () {
		$this->request->addOption('d');
		$this->request->parse();
		return true;
	}

	public function execute () {
		try {
			$this->getDatabase()->optimize();
		} catch (\Exception $e) {
			$this->handleError();
		}
		return C\View::NONE;
	}

	public function handleError () {
		return C\View::NONE;
	}

	public function validate ():bool {
		return !!$this->getDatabase();
	}
}
