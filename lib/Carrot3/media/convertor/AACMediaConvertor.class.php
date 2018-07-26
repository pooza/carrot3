<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage media.convertor
 */

namespace Carrot3;

/**
 * AACへの変換
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class AACMediaConvertor extends MediaConvertor {

	/**
	 * 変換後ファイルのサフィックス
	 *
	 * @access public
	 * @return string サフィックス
	 */
	public function getSuffix ():string {
		return '.aac';
	}

	/**
	 * 変換後のクラス名
	 *
	 * @access public
	 * @return string クラス名
	 */
	public function getClass ():string {
		return 'MusicFile';
	}
}
