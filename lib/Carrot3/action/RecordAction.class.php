<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage action
 */

namespace Carrot3;

/**
 * 詳細画面用 アクションひな形
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class RecordAction extends Action {
	use KeyGenerator;

	/**
	 * 初期化
	 *
	 * Falseを返すと、例外が発生。
	 *
	 * @access public
	 * @return bool 正常終了ならTrue
	 */
	public function initialize () {
		if ($id = $this->request['id']) {
			$this->setRecordID($id);
		}

		if ($this->isCreateAction()) {
			$this->clearRecordID();
		} else if ($record = $this->getRecord()) {
			$name = StringUtils::underscorize(
				Utils::getShortClass(
					$this->getModule()->getRecordClass()
				)
			);
			$this->request->setAttribute($name, $record);
			if (!$this->isExecutable() && !$this->request->isSubmitted()) {
				$this->request->setParameters($record->getAttributes());
			}
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
					$this->title = $this->getModule()->getRecordClass('ja');
					if ($this->isCreateAction()) {
						$this->title .= '登録';
					} else {
						if ($record = $this->getRecord()) {
							if (StringUtils::isBlank($name = $record->getName())) {
								$name = '(無題)';
							}
							$this->title .= ':' . StringUtils::truncate($name, 48);
						} else {
							$this->title = $this->getName();
						}
					}
				} catch (\Exception $e) {
					$this->title = $this->getName();
				}
			}
		}
		return $this->title;
	}

	/**
	 * ダイジェストを返す
	 *
	 * @access public
	 * @return string ダイジェスト
	 */
	public function digest ():?string {
		return $this->createKey([
			$this->getModule()->getName(),
			$this->getName(),
			$this->getRecord()->getID(),
			$this->getRecord()->getUpdateDate()->getTimestamp(),
		]);
	}

	/**
	 * 更新レコードのフィールド値を配列で返す
	 *
	 * @access protected
	 * @return Tuple フィールド値の連想配列
	 */
	protected function getRecordValues () {
		return $this->getRecord()->getAttributes();
	}

	/**
	 * 編集中レコードを返す
	 *
	 * @access public
	 * @return Record 編集中レコード
	 */
	public function getRecord () {
		return $this->getModule()->getRecord();
	}

	/**
	 * レコードを登録する為のアクションか？
	 *
	 * @access protected
	 * @return bool レコードを登録する為のアクションならTrue
	 */
	protected function isCreateAction ():bool {
		return mb_ereg('^Create', $this->getName());
	}

	/**
	 * レコードを更新
	 *
	 * @access protected
	 */
	protected function updateRecord () {
		if ($this->isCreateAction()) {
			$id = $this->getTable()->createRecord($this->getRecordValues());
			$this->setRecordID($id);
		} else {
			$this->getRecord()->update($this->getRecordValues());
		}
		$this->getRecord()->setAttachments($this->request);
	}

	/**
	 * 論理バリデーション
	 *
	 * レコードが存在するか、最低限チェックする。
	 *
	 * @access public
	 * @return bool 妥当な入力ならTrue
	 */
	public function validate ():bool {
		if (!$this->isCreateAction() && !$this->getRecord()) {
			$this->request->setError($this->getTable()->getKeyField(), '未登録です。');
			$this->controller->setHeader('Status', HTTP::getStatus(404));
			return false;
		}
		return parent::validate();
	}
}
