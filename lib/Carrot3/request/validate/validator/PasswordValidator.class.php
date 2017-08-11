<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.validate.validator
 */

namespace Carrot3;

/**
 * パスワードバリデータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class PasswordValidator extends RegexValidator {

	/**
	 * 初期化
	 *
	 * @access public
	 * @param string[] $params パラメータ配列
	 */
	public function initialize ($params = []) {
		if (!isset($params['digits'])) {
			$params['digits'] = 8;
		}
		$params['match'] = true;
		$params['match_error'] = $params['digits'] . '桁以上の英数字を入力して下さい。';
		$params['pattern'] = '[[:ascii:]]{' . $params['digits'] . ',}';

		return Validator::initialize($params);
	}
}

