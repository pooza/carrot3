<?php
namespace Carrot3;

class CarrotURL extends HTTPURL {
	private $module;
	private $action;
	private $id;

	protected function __construct ($contents = null) {
		$this->attributes = Tuple::create();
		$this->query = new WWWFormRenderer;
		$this->setContents($contents);
	}

	public function setAttribute (string $name, $value) {
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

	public function setContents ($contents) {
		if (!StringUtils::isBlank($contents)) {
			if (is_string($contents)) {
				throw new Exception('CarrotURLは文字列から生成できません。');
			}
			if (is_iterable($contents)) {
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

	public function getModuleName () {
		return $this->module;
	}

	public function setModuleName ($module) {
		if ($module instanceof Module) {
			$this->module = $module->getName();
		} else {
			$this->module = $module;
		}
		$this->action = null;
		$this->parsePath();
		return $this;
	}

	public function getActionName ():?string {
		return $this->action;
	}

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

	public function getRecordID () {
		return $this->id;
	}

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
