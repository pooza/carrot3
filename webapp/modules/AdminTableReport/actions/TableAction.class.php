<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage AdminTableReport
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

namespace Carrot3\AdminTableReportModule;
use \Carrot3 as C;

class TableAction extends C\Action {
	private $database;
	private $tableProfile;

	public function getTitle () {
		return 'テーブル:' . $this->getTableProfile()->getName();
	}

	private function getDatabase () {
		if (!$this->database) {
			$this->database = C\Database::getInstance($this->request['database']);
		}
		return $this->database;
	}

	private function getTableProfile () {
		if (!$this->tableProfile) {
			$this->tableProfile = $this->getDatabase()->getTableProfile($this->request['table']);
		}
		return $this->tableProfile;
	}

	public function execute () {
		$this->request->setAttribute('database', $this->getDatabase());
		$this->request->setAttribute('table', $this->getTableProfile());
		return C\View::SUCCESS;
	}

	public function handleError () {
		return $this->controller->getAction('not_found')->forward();
	}

	public function validate ():bool {
		return !!$this->getTableProfile();
	}
}
