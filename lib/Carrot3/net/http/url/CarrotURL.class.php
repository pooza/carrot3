<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.http.url
 */

namespace Carrot3;

/**
 * CarrotアプリケーションのURL
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class CarrotURL extends HTTPURL {
	private $module;
	private $action;
	private $id;

	/**
	 * 属性を設定
	 *
	 * @access public
	 * @param string $name 属性の名前
	 * @param mixed $value 値
	 * @return CarrotURL 自分自身
	 */
	public function setAttribute ($name, $value) {
		switch ($name) {
			case 'module':
			case 'module_name':
				return $this->setModuleName($value);
			case 'action':
			case 'action_name':
				return $this->setActionName($value);
			case 'record':
			case 'record_id':
				return $this->setRecordID($value);
		}
		return parent::setAttribute($name, $value);
	}

	/**
	 * URLを設定
	 *
	 * @access public
	 * @param mixed $contents URL
	 */
	public function setContents ($contents) {
		if (!StringUtils::isBlank($contents)) {
			if (is_string($contents)) {
				throw new NetException('CarrotURLは文字列から生成できません。');
			}
			if (is_array($contents) || ($contents instanceof ParameterHolder)) {
				$contents = Tuple::create($contents);
			}
			if (StringUtils::isBlank($contents['module'])) {
				if (StringUtils::isBlank($contents['action'])) {
					$action = $this->controller->getAction();
					$contents['action'] = $action->getName();
					$contents['module'] = $action->getModule()->getName();
				} else {
					$contents['module'] = $this->controller->getModule();
				}
			}
		}
		parent::setContents($contents);
	}

	/**
	 * モジュール名を返す
	 *
	 * @access public
	 * @return string モジュール名
	 */
	public function getModuleName () {
		if (!$this->module) {
			$this->module = BS_MODULE_DEFAULT_MODULE;
		}
		return $this->module;
	}

	/**
	 * モジュール名を設定
	 *
	 * @access public
	 * @param mixed $module モジュール又はその名前
	 * @return CarrotURL 自分自身
	 */
	public function setModuleName ($module) {
		if ($module instanceof Module) {
			$this->module = $module->getName();
		} else {
			$this->module = $module;
		}
		$this->parsePath();
		return $this;
	}

	/**
	 * アクション名を返す
	 *
	 * @access public
	 * @return string アクション名
	 */
	public function getActionName () {
		if (!$this->action) {
			$this->action = BS_MODULE_DEFAULT_ACTION;
		}
		return $this->action;
	}

	/**
	 * アクション名を設定
	 *
	 * @access public
	 * @param mixed $action アクション又はその名前
	 * @return CarrotURL 自分自身
	 */
	public function setActionName ($action) {
		if ($action instanceof Action) {
			$this->module = $action->getModule()->getName();
			$this->action = $action->getName();
		} else {
			$this->action = $action;
		}
		$this->parsePath();
		return $this;
	}

	/**
	 * レコードのIDを返す
	 *
	 * @access public
	 * @return integer レコードのID
	 */
	public function getRecordID () {
		return $this->id;
	}

	/**
	 * レコードのIDを設定
	 *
	 * @access public
	 * @param mixed $id レコード又はそのID
	 * @return CarrotURL 自分自身
	 */
	public function setRecordID ($id) {
		if ($id instanceof Record) {
			$this->id = $id->getID();
		} else {
			$this->id = $id;
		}
		$this->parsePath();
		return $this;
	}

	private function parsePath () {
		$path = Tuple::create();
		$path[] = null;
		$path[] = $this->getModuleName();
		$path[] = $this->getActionName();
		if ($id = $this->getRecordID()) {
			$path[] = $id;
		}

		// path属性をsetAttributeすると、queryやflagmentが初期化されてしまう。
		$this->attributes['path'] = $path->join('/');
	}
}

