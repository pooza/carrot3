<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.filter
 */

namespace Carrot3;

/**
 * 抽象リクエストフィルタ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class RequestFilter extends Filter {

	/**
	 * 変換して返す
	 *
	 * @access protected
	 * @param mixed $key フィールド名
	 * @param mixed $value 変換対象の文字列又は配列
	 * @return mixed 変換後
	 * @abstract
	 */
	abstract protected function convert ($key, $value);

	/**
	 * 配列を対象とするか
	 *
	 * @access protected
	 * @return bool 配列を対象とするならTrue
	 * @abstract
	 */
	protected function hasListSupport ():bool {
		return false;
	}

	public function execute () {
		foreach ($this->request->getParameters() as $key => $value) {
			if (!StringUtils::isBlank($value) && (!is_iterable($value) || $this->hasListSupport())) {
				$this->request[$key] = $this->convert($key, $value);
			}
		}
	}
}
