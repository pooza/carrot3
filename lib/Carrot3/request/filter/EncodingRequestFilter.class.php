<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.filter
 */

namespace Carrot3;

/**
 * エンコーディング リクエストフィルタ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class EncodingRequestFilter extends RequestFilter {

	/**
	 * 変換して返す
	 *
	 * @access protected
	 * @param mixed $key フィールド名
	 * @param mixed $value 変換対象の文字列又は配列
	 * @return mixed 変換後
	 */
	protected function convert ($key, $value) {
		if (StringUtils::isBlank($encoding = $this->request['encoding'])) {
			$encoding = $this->request->getUserAgent()->getDefaultEncoding();
		}
		return StringUtils::convertEncoding($value, 'utf-8', $encoding);
	}

	/**
	 * 配列を対象とするか
	 *
	 * @access protected
	 * @return bool 配列を対象とするならTrue
	 * @abstract
	 */
	protected function hasListSupport () {
		return true;
	}

	public function execute () {
		if (!ini_get('mbstring.encoding_translation') || $this['force']) {
			parent::execute();
		}
	}
}
