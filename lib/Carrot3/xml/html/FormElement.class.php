<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage xml.html
 */

namespace Carrot3;

/**
 * form要素
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class FormElement extends HTMLElement {
	const ATTACHABLE_TYPE = 'multipart/form-data';
	const SUBMITTED_FIELD = '_submitted';

	/**
	 * @access public
	 * @param string $name 要素の名前
	 * @param UserAgent $useragent 対象UserAgent
	 */
	public function __construct ($name = null, UserAgent $useragent = null) {
		parent::__construct($name);
		foreach ($this->getUserAgent()->getQuery() as $key => $value) {
			$this->addHiddenField($key, $value);
		}
	}

	/**
	 * メソッドを返す
	 *
	 * @access public
	 * @return string method属性
	 */
	public function getMethod () {
		return $this->getAttribute('method');
	}

	/**
	 * メソッドを設定
	 *
	 * @access public
	 * @param string $method メソッド
	 */
	public function setMethod ($method) {
		$this->setAttribute('method', StringUtils::toLower($method));
		if (!HTTPRequest::isValidMethod($this->getMethod())) {
			throw new HTTPException($this->getMethod() . 'は正しくないメソッドです。');
		}
		if ($this->getMethod() == 'post') {
			$this->addSubmitFields();
		}
	}

	/**
	 * フォームアクションを返す
	 *
	 * @access public
	 * @return string action属性
	 */
	public function getAction () {
		return $this->getAttribute('action');
	}

	/**
	 * フォームアクションを設定
	 *
	 * @access public
	 * @param mixed $action 文字列、URL、パラメータ配列等
	 */
	public function setAction ($action) {
		if ($action instanceof HTTPRedirector) {
			$this->setAttribute('action', $action->getURL()->getContents());
		} else if ($action instanceof ParameterHolder) {
			if (StringUtils::isBlank($action['path'])) {
				$this->setAction(URL::create($action, 'carrot'));
			} else {
				$this->setAction($action['path']);
			}
		} else {
			$this->setAttribute('action', $action);
		}
	}

	/**
	 * ファイル添付が可能か？
	 *
	 * @access public
	 * @return bool 可能ならTrue
	 */
	public function isAttachable () {
		return $this->getAttribute('enctype') == self::ATTACHABLE_TYPE;
	}

	/**
	 * ファイル添付が可能かを設定
	 *
	 * @access public
	 * @param bool $flag ファイル添付が可能ならTrue
	 */
	public function setAttachable (bool $flag) {
		if ($flag) {
			$this->setAttribute('enctype', self::ATTACHABLE_TYPE);
		} else {
			$this->removeAttribute('enctype');
		}
	}

	/**
	 * Submit判定用のhidden値を加える
	 *
	 * @access public
	 */
	public function addSubmitFields () {
		$this->addHiddenField('dummy', '符号形式識別用文字列');
		$this->addHiddenField(self::SUBMITTED_FIELD, 1);
	}

	/**
	 * hidden値を加える
	 *
	 * @access public
	 * @param string $name 名前
	 * @param string $value 値
	 * @return XMLElement 追加されたinput要素
	 */
	public function addHiddenField ($name, $value) {
		$hidden = $this->createElement('input');
		$hidden->setEmptyElement(true);
		$hidden->setAttribute('type', 'hidden');
		$hidden->setAttribute('name', $name);
		$hidden->setAttribute('value', $value);
		return $hidden;
	}
}
