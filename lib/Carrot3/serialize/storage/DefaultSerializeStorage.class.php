<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage serialize.storage
 */

namespace Carrot3;

/**
 * 規定シリアライズストレージ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class DefaultSerializeStorage implements SerializeStorage {
	use BasicObject;
	private $attributes;
	private $serializer;

	/**
	 * @access public
	 * @param Serializer $serializer
	 */
	public function __construct (Serializer $serializer = null) {
		if (!$serializer) {
			$serializer = $this->loader->createObject(BS_SERIALIZE_SERIALIZER . 'Serializer');
		}
		$this->serializer = $serializer;
		$this->attributes = Tuple::create();
	}

	/**
	 * 初期化
	 *
	 * @access public
	 * @return string 利用可能ならTrue
	 */
	public function initialize () {
		$this->getDirectory()->setDefaultSuffix($this->serializer->getSuffix());
		return $this->getDirectory()->isWritable();
	}

	private function getDirectory () {
		return FileUtils::getDirectory('serialized');
	}

	/**
	 * 属性を設定
	 *
	 * @access public
	 * @param string $name 属性の名前
	 * @param mixed $value 値
	 * @return string シリアライズされた値
	 */
	public function setAttribute ($name, $value) {
		$file = $this->getDirectory()->createEntry($name);
		$file->setContents($serialized = $this->serializer->encode($value));
		$this->attributes[$name] = $value;
		return $serialized;
	}

	/**
	 * 属性を削除
	 *
	 * @access public
	 * @param string $name 属性の名前
	 */
	public function removeAttribute ($name) {
		if ($file = $this->getDirectory()->getEntry($name)) {
			$file->delete();
		}
		$this->attributes->removeParameter($name);
	}

	/**
	 * 属性を返す
	 *
	 * @access public
	 * @param string $name 属性の名前
	 * @param Date $date 比較する日付 - この日付より古い属性値は破棄
	 * @return mixed 属性値
	 */
	public function getAttribute ($name, Date $date = null) {
		if (!$this->attributes->hasParameter($name)) {
			if (($file = $this->getDirectory()->getEntry($name)) && $file->isReadable()) {
				if (!$date || !$file->getUpdateDate()->isPast($date)) {
					$this->attributes[$name] = $this->serializer->decode($file->getContents());
				}
			}
		}
		return $this->attributes[$name];
	}

	/**
	 * 属性の更新日を返す
	 *
	 * @access public
	 * @param string $name 属性の名前
	 * @return Date 更新日
	 */
	public function getUpdateDate ($name) {
		if ($file = $this->getDirectory()->getEntry($name)) {
			return $file->getUpdateDate();
		}
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return '規定シリアライズストレージ';
	}
}

