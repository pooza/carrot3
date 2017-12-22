<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage AdminTableReport
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

namespace Carrot3\AdminTableReportModule;
use \Carrot3 as C;

class DatabaseListAction extends C\Action {
	public function getTitle () {
		return 'データベース一覧';
	}

	public function execute () {
		$this->request->setAttribute('databases', C\Database::getDatabases());
		return C\View::SUCCESS;
	}
}
