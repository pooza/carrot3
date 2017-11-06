<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage Console
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

namespace Carrot3\ConsoleModule;
use \Carrot3 as C;

class PurgeAction extends C\Action {
	public function execute () {
		$dirs = C\DirectoryLayout::getInstance();
		foreach ($dirs->getEntries() as $name => $values) {
			if ($values['purge']) {
				$date = C\Date::create();
				foreach ($values['purge'] as $key => $value) {
					$date[$key] = '-' . $value;
				}
				$dirs[$name]->purge($date);
			}
		}
		return C\View::NONE;
	}
}
