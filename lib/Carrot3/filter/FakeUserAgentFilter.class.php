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
		if (StringUtils::isBlank($this->request[WebKitUserAgent::ACCESSOR])) {
			$flag = !!$this->user->getAttribute(WebKitUserAgent::ACCESSOR);
		} else {
			$flag = !!$this->request[WebKitUserAgent::ACCESSOR];
		}
		$this->user->setAttribute(WebKitUserAgent::ACCESSOR, $flag);
		if ($flag) {
			$this->request->setUserAgent(
				UserAgent::create(WebKitUserAgent::DEFAULT_NAME)
			);
		}
	}
}
