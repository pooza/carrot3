<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage serialize.storage
 */

namespace Carrot3;

/**
 * ファイルシリアライズストレージ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class FileSerializeStorage extends SerializeStorage {
	private $attributes;

	/**
	 * 初期化
	 *
	 * @access public
	 * @param SerializeHandler $handler
	 * @return bool 利用可能ならTrue
	 */
	public function initialize (SerializeHandler $handler):bool {
		parent::initialize($handler);
		$this->attributes = Tuple::create();
		$this->getDirectory()->setDefaultSuffix($this->getSerializer()->getSuffix());
		return $this->getDirectory()->isWritable();
	}

	private function getDirectory ():Directory {
		return FileUtils::getDirectory('serialized');
	}

	/**
	 * 属性を設定
	 *
	 * @access public
	 * @param string $name 属性の名前
	 * @param mixed $value 値
	 */
	public function setAttribute (string $name, $value) {
		$file = $this->getDirectory()->createEntry($name);
		$file->setContents($serialized = $this->getSerializer()->encode($value));
		$this->attributes[$name] = $value;
	}

	/**
	 * 属性を削除
	 *
	 * @access public
	 * @param string $name 属性の名前
	 */
	public function removeAttribute (string $name) {
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
	public function getAttribute (string $name, Date $date = null) {
		if (!$this->attributes->hasParameter($name)) {
			if (($file = $this->getDirectory()->getEntry($name)) && $file->isReadable()) {
				if (!$date || !$file->getUpdateDate()->isPast($date)) {
					$this->attributes[$name] = $this->getSerializer()->decode(
						$file->getContents()
					);
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
	public function getUpdateDate (string $name):?Date {
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
