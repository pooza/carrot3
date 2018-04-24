<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage file
 */

namespace Carrot3;

/**
 * シリアライズ可能なファイル
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
trait SerializableFile {
	use SerializableObject;

	/**
	 * ダイジェストを返す
	 *
	 * @access public
	 * @return string ダイジェスト
	 */
	public function digest ():?string {
		return $this->getID();
	}

	/**
	 * シリアライズ時の値を返す
	 *
	 * @access public
	 * @return Tuple シリアライズ時の値
	 */
	public function getSerialized ():?Tuple {
		if ($this->isExists()) {
			$date = $this->getUpdateDate();
			if ($value = (new SerializeHandler)->getAttribute($this, $date)) {
				return Tuple::create($value);
			}
		}
		$this->removeSerialized();
		return null;
	}
}
