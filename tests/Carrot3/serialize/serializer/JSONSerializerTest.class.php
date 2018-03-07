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
			$this->assert(
				'encode1',
				$serializer->encode(['key' => '木の水晶球']) == '{"key":"木の水晶球"}'
			);
			$this->assert(
				'encode2',
				$serializer->encode(['key' => '\\']) == '{"key":"\\\\"}'
			);
		}
	}
}
