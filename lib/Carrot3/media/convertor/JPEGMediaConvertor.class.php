<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage media.convertor
 */

namespace Carrot3;

/**
 * JPEGへの変換
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class JPEGMediaConvertor extends MediaConvertor {

	/**
	 * 変換後ファイルのサフィックス
	 *
	 * @access public
	 * @return string サフィックス
	 */
	public function getSuffix ():string {
		return '.jpg';
	}

	/**
	 * 変換後のクラス名
	 *
	 * @access public
	 * @return string クラス名
	 */
	public function getClass () {
		return 'ImageFile';
	}

	/**
	 * 変換して返す
	 *
	 * @access public
	 * @param MovieFile $source 変換後ファイル
	 * @return MediaFile 変換後ファイル
	 */
	public function execute (MediaFile $source) {
		if ($source['duration'] < $this->getConstant('ss')) {
			$this->setConfig('ss', 0);
		}
		return parent::execute($source);
	}
}
