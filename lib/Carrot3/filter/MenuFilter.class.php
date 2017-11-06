<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage filter
 */

namespace Carrot3;

/**
 * メニュー構築フィルタ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class MenuFilter extends Filter {
	private $menu;

	public function execute () {
		$this->request->setAttribute('menu', $this->getMenu());
	}

	private function getMenu () {
		if (!$this->menu) {
			$this->menu = Tuple::create();
			$separator = true; //次の仕切りを無視するか？
			foreach (ConfigManager::getInstance()->compile($this->getMenuFile()) as $values) {
				if ($menuitem = $this->getMenuItem($values)) {
					if (StringUtils::isBlank($menuitem['separator'])) {
						$separator = false;
					} else {
						if ($separator) {
							continue;
						}
						$separator = true;
					}
					$this->menu[] = $menuitem;
				}
			}
		}
		return $this->menu;
	}

	private function getMenuItem ($values) {
		$values = Tuple::create($values);
		if (!StringUtils::isBlank($values['module'])) {
			if (!$module = $this->controller->getModule($values['module'])) {
				$message = new StringFormat('モジュール "%s" がありません。');
				$message[] = $values['module'];
				throw new ConfigException($message);
			}
			if (StringUtils::isBlank($values['title'])) {
				$values['title'] = $module->getMenuTitle();
			}
			if (StringUtils::isBlank($values['credential'])) {
				$values['credential'] = $module->getCredential();
			}
		}
		if (!StringUtils::isBlank($values['href'])) {
			$url = URL::create();
			$url['path'] = $values['href'];
			if(StringUtils::isBlank($value = $url->getParameter(UserAgent::ACCESSOR))) {
				$useragent = $this->request->getUserAgent();
			} else {
				$useragent = UserAgent::create($value);
			}
			$url->setParameters($useragent->getQuery());
			$values['href'] = $url->getFullPath();
		}
		if ($this->user->hasCredential($values['credential'])) {
			return $values;
		}
	}

	private function getMenuFile () {
		$names = Tuple::create([
			$this['name'],
			StringUtils::pascalize($this->getModule()->getPrefix()),
			StringUtils::underscorize($this->getModule()->getPrefix()),
		]);
		foreach ($names as $name) {
			if ($file = ConfigManager::getConfigFile('menu/' . $name)) {
				return $file;
			}
		}

		$message = new StringFormat('メニュー (%s)が見つかりません。');
		$message[] = $names->join('|');
		throw new ConfigException($message);
	}

	private function getModule () {
		return $this->controller->getModule();
	}
}
