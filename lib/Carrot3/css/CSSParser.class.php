<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage css
 */

namespace Carrot3;

/**
 * CSSパーサー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class CSSParser extends ParameterHolder {
	private $contents;

	/**
	 * @access public
	 * @param string $contents
	 */
	public function __construct ($contents = null) {
		$this->setContents($contents);
	}

	/**
	 * 内容を設定
	 *
	 * @access public
	 * @param string $contents
	 */
	public function setContents ($contents) {
		$this->contents = $contents;
		$this->clear();
		$contents = StringUtils::stripHTMLComment($this->contents);
		$contents = mb_ereg_replace('\\s+', ' ', $contents, 'm');
 		$pattern = '([-[:alnum:].#:,>@\n ]+?)\\s*{(.*?)}';
		foreach (StringUtils::eregMatchAll($pattern, $contents) as $matches) {
			$this[$matches[1]] = Tuple::create();
			foreach (StringUtils::explode(';', $matches[2]) as $prop) {
				$prop = StringUtils::explode(':', $prop);
				if (!StringUtils::isBlank($value = trim($prop[1]))) {
					$this[$matches[1]][trim($prop[0])] = $value;
				}
			}
		}
	}
}
