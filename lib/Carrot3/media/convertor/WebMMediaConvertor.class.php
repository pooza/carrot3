<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage media.convertor
 */

namespace Carrot3;

/**
 * WebMへの変換
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class WebMMediaConvertor extends MediaConvertor {

	/**
	 * 変換後のクラス名
	 *
	 * @access public
	 * @return string クラス名
	 */
	public function getClass ():string {
		return 'MovieFile';
	}
}
