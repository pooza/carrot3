<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage AdminTableReport
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

namespace Carrot3\AdminTableReportModule;
use \Carrot3 as C;

class DatabaseAction extends C\Action {
	private $database;

	public function getTitle () {
		return 'データベース:' . $this->getDatabase()->getName();
	}

	private function getDatabase () {
		if (!$this->database) {
			$this->database = C\Database::getInstance($this->request['database']);
		}
		return $this->database;
	}

	public function execute () {
		$this->request->setAttribute('database', $this->getDatabase());
		return C\View::SUCCESS;
	}

	public function handleError () {
		return $this->controller->getAction('not_found')->forward();
	}

	public function validate () {
		return !!$this->getDatabase();
	}
}
