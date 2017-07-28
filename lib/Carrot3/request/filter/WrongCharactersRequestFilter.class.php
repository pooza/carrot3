<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.filter
 */

namespace Carrot3;

/**
 * 機種依存文字 リクエストフィルタ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class WrongCharactersRequestFilter extends RequestFilter {

	/**
	 * 変換して返す
	 *
	 * @access protected
	 * @param mixed $key フィールド名
	 * @param mixed $value 変換対象の文字列又は配列
	 * @return mixed 変換後
	 */
	protected function convert ($key, $value) {
		return StringUtils::convertWrongCharacters($value);
	}
}

