<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class MIMEUtilsTest extends Test {
	public function execute () {
		$this->assert('decode', MIMEUtils::decode('=?utf-8?B?5pel5pys6Kqe44Gu44Oh44O844Or?=') == '日本語のメール');
		$this->assert('encode', MIMEUtils::encode('日本語のメール1') == '=?utf-8?B?5pel5pys6Kqe44Gu44Oh44O844Or?=1');
	}
}
