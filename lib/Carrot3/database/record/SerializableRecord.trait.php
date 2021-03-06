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
	use SerializableObject, KeyGenerator;

	/**
	 * ダイジェストを返す
	 *
	 * @access public
	 * @return string ダイジェスト
	 */
	public function digest ():?string {
		return $this->createKey([
			$this->getID(),
			$this->getUpdateDate()->getTimestamp(),
		]);
	}

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
		$this->removeSerialized();
		return null;
	}

	/**
	 * 全てのファイル属性
	 *
	 * @access protected
	 * @return Tuple ファイル属性の配列
	 */
	protected function createSerializableValues () {
		$values = Tuple::create($this->getAttributes());
		$values['is_visible'] = $this->isVisible();
		if ($url = $this->getURL()) {
			$values['url'] = $url->getContents();
		}
		foreach ($this->getTable()->getImageNames() as $field) {
			if (!!$this->getImageFile($field)) {
				$values['has_' . $field] = true;
				$values[$field] = $this->getImageInfo($field);
			}
		}
		foreach ($this->getTable()->getAttachmentNames() as $field) {
			if ($file = $this->getAttachment($field)) {
				$values['has_' . $field] = true;
				if ($file instanceof Assignable) {
					$values[$field] = $file->assign();
				}
			}
		}
		return $values;
	}

	/**
	 * アサインすべき値を返す
	 *
	 * @access public
	 * @return Tuple アサインすべき値
	 */
	public function assign () {
		if (!$this->isSerialized()) {
			$this->serialize();
		}
		return $this->getSerialized();
	}
}
