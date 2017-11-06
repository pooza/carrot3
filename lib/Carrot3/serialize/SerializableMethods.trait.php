<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage database.table
 */

namespace Carrot3;

/**
 * シリアライズ可能なオブジェクト
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
trait SerializableMethods {

	/**
	 * シリアライズ時の値を返す
	 *
	 * @access public
	 * @return mixed シリアライズ時の値
	 */
	public function getSerialized () {
		return Controller::getInstance()->getAttribute($this);
	}

	/**
	 * シリアライズされたキャッシュを削除
	 *
	 * @access public
	 */
	public function removeSerialized () {
		Controller::getInstance()->removeAttribute($this);
	}
}
