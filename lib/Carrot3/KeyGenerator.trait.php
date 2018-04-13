<?php
/**
 * @package jp.co.b-shock.carrot3
 */

namespace Carrot3;

/**
 * キー生成
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
trait KeyGenerator {

	/**
	 * シリアライズのダイジェストを返す
	 *
	 * @access protected
	 * @param iterable $values 属性名に用いる値
	 * @return string 属性名
	 */
	protected function createKey (iterable $values):string {
		$values = Tuple::create($values);
		$values[] = Controller::getInstance()->getHost()->getName();
		$values[] = Utils::getClass($this);
		$values[] = __FUNCTION__;
		return Crypt::digest(
			(new PHPSerializer)->encode($values->decode())
		);
	}
}
