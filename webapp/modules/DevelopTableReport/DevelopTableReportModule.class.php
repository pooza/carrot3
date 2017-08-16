<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage DevelopTableReport
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

namespace Carrot3\DevelopTableReportModule;
use \Carrot3 as C;

class DevelopTableReportModule extends C\Module {
	public function getTitle () {
		return 'TableReport管理モジュール';
	}

	public function getMenuTitle () {
		return 'TableReport';
	}
}

