<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage filter
 */

namespace Carrot3;

/**
 * 規定フィルタセット
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
class FilterSet extends Tuple {
	use BasicObject;

	/**
	 * @access public
	 */
	public function __construct () {
		foreach ($this->getConfigFiles() as $file) {
			if ($filters = ConfigManager::getInstance()->compile($file)) {
				foreach ($filters as $filter) {
					$this[] = $filter;
				}
			}
		}
		$this[] = new ExecutionFilter;
	}

	/**
	 * フィルタ設定ファイルの配列を返す
	 *
	 * @access protected
	 * @return Tuple 設定ファイルの配列
	 */
	protected function getConfigFiles ():Tuple {
		$files = Tuple::create();
		$files[] = 'filters/carrot';
		$files[] = 'filters/application';
		$files[] = 'filters/' . $this->controller->getHost()->getName();

		if ($file = $this->controller->getModule()->getConfigFile('filters')) {
			$files[] = $file;
		}
		return $files;
	}

	/**
	 * 実行
	 *
	 * @access public
	 */
	public function execute () {
		foreach ($this as $filter) {
			if ($filter->isExecutable()) {
				if ($filter->execute() == Controller::COMPLETED) {
					exit;
				}
				$filter->setExecuted();
			}
		}
	}

	/**
	 * 要素を設定
	 *
	 * @access public
	 * @param string $name 名前
	 * @param mixed $filter 要素（フィルタ）
	 * @param bool $position 先頭ならTrue
	 */
	public function setParameter (?string $name, $filter, bool $position = self::POSITION_BOTTOM) {
		if (($filter instanceof Filter) == false) {
			throw new FilterException('フィルターセットに加えられません。');
		}
		if (StringUtils::isBlank($name)) {
			$name = $filter->getName();
		}
		parent::setParameter($name, $filter, $position);
	}
}
