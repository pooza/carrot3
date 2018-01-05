<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage AdminTableReport
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

namespace Carrot3\AdminTableReportModule;
use \Carrot3 as C;

class AdminTableReportModule extends C\Module {
	public function getTitle () {
		return 'データベースモジュール';
	}

	public function getMenuTitle () {
		return 'データベース';
	}
}
