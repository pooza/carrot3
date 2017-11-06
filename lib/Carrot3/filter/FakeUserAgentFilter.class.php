<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage filter
 */

namespace Carrot3;

/**
 * 偽装されたUserAgent
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class FakeUserAgentFilter extends Filter {
	public function execute () {
		if (BS_DEBUG || $this->user->isAdministrator()) {
			if (!StringUtils::isBlank($name = $this->request[UserAgent::ACCESSOR])) {
				$this->request->setUserAgent(UserAgent::create($name));
				return;
			}
		}

		$names = Tuple::create([
			TridentUserAgent::ACCESSOR => TridentUserAgent::DEFAULT_NAME,
			WebKitUserAgent::ACCESSOR => WebKitUserAgent::DEFAULT_NAME,
		]);
		foreach ($names as $field => $name) {
			if ($this->request[$field] || $this->user->getAttribute($field)) {
				$this->user->setAttribute($field, 1);
				$this->request->setUserAgent(UserAgent::create($name));
				break;
			}
		}
	}
}
