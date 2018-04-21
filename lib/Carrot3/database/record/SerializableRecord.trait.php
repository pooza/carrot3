<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage database.record
 */

namespace Carrot3;

/**
 * シリアライズ可能なレコード
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
trait SerializableRecord {

	/**
	 * シリアライズ時の値を返す
	 *
	 * @access public
	 * @return Tuple シリアライズ時の値
	 */
	public function getSerialized ():?Tuple {
		if ($date = $this->getUpdateDate()) {
			if ($value = (new SerializeHandler)->getAttribute($this, $date)) {
				return Tuple::create($value);
			}
		} else {
			if ($value = (new SerializeHandler)[$this]) {
				return Tuple::create($value);
			}
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
