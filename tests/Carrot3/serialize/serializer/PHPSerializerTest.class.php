<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class PHPSerializerTest extends Test {
	public function execute () {
		$serializer = new PHPSerializer;
		if ($serializer->initialize()) {
			$encoded = $serializer->encode(['key' => '木の水晶球']);
			$this->assert('encode', $encoded == 'a:1:{s:3:"key";s:15:"木の水晶球";}');
		}
	}
}
