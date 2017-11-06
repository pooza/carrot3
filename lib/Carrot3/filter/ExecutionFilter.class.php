<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage filter
 */

namespace Carrot3;

/**
 * アクション実行
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ExecutionFilter extends Filter {
	public function execute () {
		if ($this->action->isCacheable()) {
			$manager = RenderManager::getInstance();
			if ($view = $manager->getCache($this->action)) {
				$this->doView($view);
			} else {
				if ($view = $this->doAction()) {
					$manager->cache($this->doView($view));
				} else {
					$this->doView($view);
				}
			}
		} else {
			$this->doView($this->doAction());
		}
		return Controller::COMPLETED;
	}

	private function doAction () {
		if ($this->action->isExecutable()) {
			if ($file = $this->action->getValidationFile()) {
				ConfigManager::getInstance()->compile($file);
			}
			$this->action->registerValidators();
			if (!ValidateManager::getInstance()->execute() || !$this->action->validate()) {
				return $this->action->handleError();
			}
			ini_set('memory_limit', $this->action->getMemoryLimit());
			set_time_limit($this->action->getTimeLimit());
			return $this->action->execute();
		} else {
			return $this->action->getDefaultView();
		}
	}

	private function doView ($view) {
		if (!($view instanceof View)) {
			$view = $this->action->getView($view);
		}
		if (!$view->initialize()) {
			throw new ViewException($view . 'が初期化できません。');
		}
		$view->execute();
		$view->render();
		return $view;
	}

	/**
	 * 二度目も実行するか
	 *
	 * @access public
	 * @return boolean 二度目も実行するならTrue
	 */
	public function isRepeatable () {
		return true;
	}
}
