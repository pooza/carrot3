<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage media.convertor
 */

namespace Carrot3;

/**
 * MP3への変換
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class MP3MediaConvertor extends MediaConvertor {

	/**
	 * 変換後のクラス名
	 *
	 * @access public
	 * @return string クラス名
	 */
	public function getClass () {
		return 'MusicFile';
	}
}
