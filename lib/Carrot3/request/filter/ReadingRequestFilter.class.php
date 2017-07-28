<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.filter
 */

namespace Carrot3;

/**
 * フリガナ リクエストフィルタ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ReadingRequestFilter extends RequestFilter {

	/**
	 * 変換して返す
	 *
	 * @access protected
	 * @param mixed $key フィールド名
	 * @param mixed $value 変換対象の文字列又は配列
	 * @return mixed 変換後
	 */
	protected function convert ($key, $value) {
		if (mb_ereg('_read$', $key)) {
			$value = str_replace(' ', '', $value);
			$value = StringUtils::convertKana($value, 'KVC');
		}
		return $value;
	}
}

