<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class JSONSerializerTest extends Test {
	public function execute () {
		$serializer = new JSONSerializer;
		if ($serializer->initialize()) {
			$encoded = $serializer->encode(['key' => '木の水晶球']);
			$this->assert('encode', $encoded == '{"key":"\u6728\u306e\u6c34\u6676\u7403"}');
		}
	}
}
