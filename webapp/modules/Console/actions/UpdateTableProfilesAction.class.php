<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage Console
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

namespace Carrot3\ConsoleModule;
use \Carrot3 as C;

class UpdateTableProfilesAction extends C\Action {
	public function execute () {
		foreach (C\Database::getDatabases() as $name => $params) {
			$db = C\Database::getInstance($name);
			foreach ($db->getTableNames() as $table) {
				$table = $db->getTableProfile($table);
				$table->serialize();
			}
		}
		C\LogManager::getInstance()->put('実行しました。', $this);
		return C\View::NONE;
	}
}

