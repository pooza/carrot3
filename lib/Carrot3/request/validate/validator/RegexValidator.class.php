<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.validate.validator
 */

namespace Carrot3;

/**
 * 正規表現バリデータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class RegexValidator extends Validator {

	/**
	 * 初期化
	 *
	 * @access public
	 * @param string[] $params パラメータ配列
	 */
	public function initialize ($params = []) {
		$this['match'] = true;
		$this['match_error'] = '正しくありません。';
		$this['pattern'] = null;
		parent::initialize($params);

		if (!$this['pattern']) {
			throw new ValidateException('正規表現パターンが指定されていません。');
		}
		return true;
	}

	/**
	 * 実行
	 *
	 * @access public
	 * @param mixed $value バリデート対象
	 * @return boolean 妥当な値ならばTrue
	 */
	public function execute ($value) {
		$matched = !!mb_ereg($this['pattern'], $value);
		if (($this['match'] && !$matched) || (!$this['match'] && $matched)) {
			$this->error = $this['match_error'];
			return false;
		}
		return true;
	}
}

