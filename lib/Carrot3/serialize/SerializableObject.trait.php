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
trait SerializableObject {

	/**
	 * シリアライズ時の値を返す
	 *
	 * @access public
	 * @return Tuple シリアライズ時の値
	 */
	public function isSerialized ():bool {
		return !StringUtils::isBlank($this->getSerialized());
	}

	/**
	 * シリアライズ時の値を返す
	 *
	 * @access public
	 * @return Tuple シリアライズ時の値
	 */
	public function getSerialized ():?Tuple {
		if ($value = (new SerializeHandler)[$this]) {
			return Tuple::create($value);
		}
		return null;
	}

	/**
	 * シリアライズされたキャッシュを削除
	 *
	 * @access public
	 */
	public function removeSerialized () {
		(new SerializeHandler)->removeAttribute($this);
	}
}
