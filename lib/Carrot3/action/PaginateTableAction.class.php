<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage action
 */

namespace Carrot3;

/**
 * ページあり一覧画面用 アクションひな形
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class PaginateTableAction extends TableAction {

	/**
	 * ページ番号を返す
	 *
	 * @access protected
	 * @return integer ページ番号
	 */
	protected function getPageNumber () {
		if (!$this->page) {
			if (!$page = $this->request['page']) {
				$page = 1;
			}
			$this->getTable()->setPageNumber($page);
			$this->page = $this->getTable()->getPageNumber();
		}
		return $this->page;
	}

	/**
	 * ページサイズを返す
	 *
	 * @access public
	 * @return integer ページサイズ
	 */
	protected function getPageSize () {
		if ($this->request->isMobile()) {
			return 10;
		} else {
			return 50;
		}
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
			$this->table->setPageSize($this->getPageSize());
			$this->table->setPageNumber($this->getPageNumber());
		}
		return $this->table;
	}

	public function initialize () {
		parent::initialize();
		$this->request->setAttribute('page', $this->getPageNumber());
		$this->request->setAttribute('lastpage', $this->getTable()->getLastPageNumber());
		return true;
	}
}

