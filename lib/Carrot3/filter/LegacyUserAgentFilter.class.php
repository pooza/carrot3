<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage filter
 */

namespace Carrot3;

/**
 * 禁止されたUserAgent
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class LegacyUserAgentFilter extends Filter {
	public function execute () {
		if ($this->request->getUserAgent()->isLegacy()) {
			//フィルタの中からはforwardできないので。
			$this->controller->register(
				$this->controller->getAction('legacy_user_agent')
			);
		}
	}
}
