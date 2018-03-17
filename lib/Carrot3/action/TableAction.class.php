<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage action
 */

namespace Carrot3;

/**
 * 一覧画面用 アクションひな形
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class TableAction extends Action {
	protected $criteria;
	protected $order;
	protected $rows;
	protected $table;
	protected $page;

	/**
	 * 初期化
	 *
	 * Falseを返すと、例外が発生。
	 *
	 * @access public
	 * @return bool 正常終了ならTrue
	 */
	public function initialize () {
		parent::initialize();
		$this->getModule()->clearRecordID();

		$params = Tuple::create();
		if (BS_MODULE_CACHE_PARAMETERS) {
			$params->setParameters($this->getModule()->getParameterCache());
		}
		$params->setParameters($this->request->getParameters());

		$this->request->setParameters($params);
		if (BS_MODULE_CACHE_PARAMETERS) {
			$this->getModule()->cacheParameters($params);
		}

		$this->assignStatusOptions();

		return true;
	}

	/**
	 * タイトルを返す
	 *
	 * @access public
	 * @return string タイトル
	 */
	public function getTitle () {
		if (StringUtils::isBlank($this->title)) {
			if (StringUtils::isBlank($this->title = $this->getConfig('title'))) {
				try {
					$this->title = $this->getModule()->getRecordClass('ja') . '一覧';
				} catch (\Exception $e) {
					$this->title = $this->getName();
				}
			}
		}
		return $this->title;
	}

	/**
	 * テーブルを返す
	 *
	 * @access public
	 * @return TableHandler テーブル
	 */
	public function getTable () {
		if (!$this->table) {
			$this->table = clone $this->getModule()->getTable();
			$this->table->setCriteria($this->getCriteria());
			$this->table->setOrder($this->getOrder());
		}
		return $this->table;
	}

	/**
	 * テーブルの内容を返す
	 *
	 * @access protected
	 * @return Tuple テーブルの内容
	 */
	protected function getRows () {
		if (!$this->rows) {
			$this->rows = Tuple::create();
			if ($this->isShowable()) {
				foreach ($this->getTable() as $record) {
					$this->rows[] = $record->assign();
				}
			}
		}
		return $this->rows;
	}

	/**
	 * 検索条件を返す
	 *
	 * @access protected
	 * @return string[] 検索条件
	 */
	protected function getCriteria () {
		return [];
	}

	/**
	 * ソート順を返す
	 *
	 * @access protected
	 * @return string[] ソート順
	 */
	protected function getOrder () {
		return [];
	}

	/**
	 * リストを表示するか
	 *
	 * @access protected
	 * @return bool 表示して良いならTrue
	 */
	protected function isShowable () {
		return !$this->request->hasErrors();
	}
}
