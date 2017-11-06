<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage database.record
 */

namespace Carrot3;

/**
 * レコード検索
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class RecordFinder extends ParameterHolder {
	use BasicObject;
	private $table;
	private $record;

	/**
	 * @access public
	 * @param ParameterHolder $params 要素の配列
	 */
	public function __construct (ParameterHolder $params) {
		$this->setParameters($params);
	}

	/**
	 * 検索実行
	 *
	 * @access public
	 * @param integer $id ID
	 * @return Record レコード
	 */
	public function execute ($id = null) {
		if (($record = $this->getRecord($id)) && ($record instanceof Record)) {
			return $record;
		}
	}

	private function getRecord ($id) {
		if (!$this->record) {
			if (!$id) {
				$id = $this['id'];
			}
			if (($table = $this->getTable()) && ($record = $table->getRecord($id))) {
				$this->record = $record;
			} else if (StringUtils::isBlank($this['class'])) {
				$this->record = $this->controller->getModule()->getRecord();
			}
		}
		return $this->record;
	}

	private function getTable () {
		if (!$this->table) {
			try {
				if (StringUtils::isBlank($class = $this['class'])) {
					$this->table = $this->controller->getModule()->getTable();
				} else {
					$this->table = TableHandler::create($class);
				}
			} catch (\Exception $e) {
			}
		}
		return $this->table;
	}
}
