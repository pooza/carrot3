<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage Console
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

namespace Carrot3\ConsoleModule;
use \Carrot3 as C;

class BackupAction extends C\Action {
	public function execute () {
		$class = $this->loader->getClass(BS_BACKUP_CLASS);
		$class::getInstance()->execute();
		return C\View::NONE;
	}
}
