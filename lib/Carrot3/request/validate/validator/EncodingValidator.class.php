<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.validate.validator
 */

namespace Carrot3;

/**
 * エンコード名バリデータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class EncodingValidator extends Validator {

	/**
	 * 初期化
	 *
	 * @access public
	 * @param string[] $params パラメータ配列
	 */
	public function initialize ($params = []) {
		$this['match_error'] = '利用できないエンコード名です。';
		return Validator::initialize($params);
	}

	/**
	 * 実行
	 *
	 * @access public
	 * @param mixed $value バリデート対象
	 * @return bool 妥当な値ならばTrue
	 */
	public function execute ($value) {
		if (StringUtils::isBlank(mb_preferred_mime_name($value))) {
			$this->error = $this['match_error'];
			return false;
		}
		return true;
	}
}
