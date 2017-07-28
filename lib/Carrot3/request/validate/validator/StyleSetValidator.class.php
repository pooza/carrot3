<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.validate.validator
 */

namespace Carrot3;

/**
 * スタイルセット選択バリデータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class StyleSetValidator extends ChoiceValidator {
	protected function getChoices () {
		$styleset = new StyleSet;
		return $styleset->getEntryNames();
	}
}

