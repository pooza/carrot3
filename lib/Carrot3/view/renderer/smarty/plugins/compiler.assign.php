<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3 as C;

/**
 * テンプレート上で、変数のアサインを行う
 *
 * 配列への代入が出来る様に拡張。
 *
 * 記述例 {assign var='array.var1' value='hoge'}
 *     → $this->_tpl_vars['array']['var1'] = 'hoge';
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_compiler_assign ($params, &$compiler) {
	$params = C\Tuple::create($compiler->_parse_attrs($params));

	if (C\StringUtils::isBlank($params['var'])) {
		$compiler->_syntax_error('assign: varが未定義です。', E_USER_WARNING);
		return;
	}
	if (!$params->hasParameter('value')) {
		$compiler->_syntax_error('assign: valueが未定義です。', E_USER_WARNING);
		return;
	}

	$var = null;
	foreach (C\StringUtils::explode('.', str_replace('.', "'.'", $params['var'])) as $part) {
		if ($part == "''") {
			$var .= '[]';
		} else {
			$var .= '[' . $part . ']';
		}
	}

	return '$this->_tpl_vars' . $var . '=' . $params['value'] . ';';
}
