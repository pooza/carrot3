<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.validate.validator
 */

namespace Carrot3;

/**
 * NGワードバリデータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class NGWordValidator extends Validator {

	/**
	 * 初期化
	 *
	 * @access public
	 * @param iterable $params パラメータ配列
	 */
	public function initialize (?iterable $params = []) {
		$this['word_error'] = '不適切な言葉が含まれています。';
		return parent::initialize($params);
	}

	/**
	 * 実行
	 *
	 * @access public
	 * @param mixed $value バリデート対象
	 * @return bool 妥当な値ならばTrue
	 */
	public function execute ($value) {
		foreach (ConfigCompiler::parseFiles('ng_word') as $word) {
			if (StringUtils::isContain($word, $value)) {
				$this->error = $this['word_error'];
				break;
			}
		}
		return StringUtils::isBlank($this->error);
	}
}
