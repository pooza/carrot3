<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage filter
 */

namespace Carrot3;

/**
 * ホスト認証
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class HostSecurityFilter extends Filter {
	private $networks;

	public function execute () {
		try {
			foreach ($this->getNetworks() as $network) {
				if ($network->isContain($this->request->getHost())) {
					return false;
				}
			}
			throw new NetException('リモートアクセス禁止のホストです。');
		} catch (NetException $e) {
			$this->controller->getAction('secure')->forward();
			return Controller::COMPLETED;
		}
	}

	private function getNetworks () {
		if (!$this->networks) {
			$this->networks = Tuple::create();
			if (StringUtils::isBlank(BS_ADMIN_NETWORKS)) {
				$this->networks[] = new Network('0.0.0.0/0');
			} else {
				$this->networks[] = new Network('127.0.0.1/32');
				foreach (StringUtils::explode(',', BS_ADMIN_NETWORKS) as $network) {
					$this->networks[] = new Network($network);
				}
			}
		}
		return $this->networks;
	}
}

