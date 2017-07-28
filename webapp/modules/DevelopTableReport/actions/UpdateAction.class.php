<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage DevelopTableReport
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

namespace Carrot3\DevelopTableReportModule;
use \Carrot3 as C;

class UpdateAction extends C\Action {
	private $database;

	private function getDatabase () {
		if (!$this->database) {
			$this->database = C\Database::getInstance($this->request['database']);
		}
		return $this->database;
	}

	public function execute () {
		foreach ($this->getDatabase()->getTableNames() as $table) {
			$this->getDatabase()->getTableProfile($table)->removeSerialized();
		}
		$url = $this->getModule()->getAction('Database')->createURL();
		$url->setParameter('database', $this->getDatabase()->getName());
		return $url->redirect();
	}

	public function handleError () {
		return $this->controller->getAction('not_found')->forward();
	}

	public function validate () {
		return !!$this->getDatabase();
	}
}

