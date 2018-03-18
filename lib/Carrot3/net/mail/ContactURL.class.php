<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mail
 */

namespace Carrot3;

/**
 * 連絡先URL
 *
 * mailtoとかtelとか。
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ContactURL extends URL {
	private $query;

	/**
	 * @access protected
	 * @param mixed $contents URL
	 */
	protected function __construct ($contents = null) {
		$this->query = new WWWFormRenderer;
		parent::__construct($contents);
	}

	/**
	 * 内容を返す
	 *
	 * @access public
	 * @return string URL
	 */
	public function getContents () {
		if (!$this->contents) {
			$this->contents = $this['scheme'] . ':' . $this['path'];
			if ($this->query->count()) {
				$this->contents .= '?' . $this->query->getContents();
			}
		}
		return $this->contents;
	}

	/**
	 * 属性を設定
	 *
	 * @access public
	 * @param string $name 属性の名前
	 * @param mixed $value 値
	 * @return URL 自分自身
	 */
	public function setAttribute (string $name, $value) {
		switch ($name) {
			case 'scheme':
			case 'path':
				$this->attributes[$name] = $value;
				break;
			case 'query':
				$this->query->setContents($value);
				break;
		}
		$this->contents = null;
		return $this;
	}
}
