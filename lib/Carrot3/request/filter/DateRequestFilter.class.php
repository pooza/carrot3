<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.filter
 */

namespace Carrot3;

/**
 * 日付 リクエストフィルタ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class DateRequestFilter extends RequestFilter {

	/**
	 * 変換して返す
	 *
	 * @access protected
	 * @param mixed $key フィールド名
	 * @param mixed $value 変換対象の文字列又は配列
	 * @return mixed 変換後
	 */
	protected function convert ($key, $value) {
		if (mb_ereg('(day|date)$', $key) && !in_array($key, ['weekday'])) {
			if ($date = Date::create($value)) {
				if ($date['hour'] || $date['minute'] || $date['second']) {
					$value = $date->format('Y-m-d H:i:s');
				} else {
					$value = $date->format('Y-m-d');
				}
			}
		}
		return $value;
	}
}
