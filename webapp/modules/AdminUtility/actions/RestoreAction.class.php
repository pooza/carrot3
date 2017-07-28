<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage AdminUtility
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

namespace Carrot3\AdminUtilityModule;
use \Carrot3 as C;

class RestoreAction extends C\Action {
	private $manager;

	/**
	 * タイムアウト時間を返す
	 *
	 * @access public
	 * @return integer タイムアウト時間(秒)、設定の必要がない場合はNULL
	 */
	public function getTimeLimit () {
		return 300;
	}

	/**
	 * タイトルを返す
	 *
	 * @access public
	 * @return string タイトル
	 */
	public function getTitle () {
		return 'リストア';
	}

	private function getBackupManager () {
		if (!$this->manager) {
			$class = $this->loader->getClass(BS_BACKUP_CLASS);
			$this->manager = $class::getInstance();
		}
		return $this->manager;
	}

	public function execute () {
		try {
			$this->getBackupManager()->restore($this->request->getUploadedFile());
		} catch (FileException $e) {
			$message = new StringFormat('リストアに失敗しました。 (%s)');
			$message[] = $e->getMessage();
			$this->request->setError('Utils', $message);
			return $this->handleError();
		}
		return C\View::SUCCESS;
	}

	public function getDefaultView () {
		$this->request->setAttribute(
			'is_restoreable',
			$this->getBackupManager()->isRestoreable()
		);
		return C\View::INPUT;
	}

	public function handleError () {
		return C\View::ERROR;
	}

	public function validate () {
		return parent::validate() && $this->getBackupManager()->isRestoreable();
	}

	public function getViewClass () {
		if ($this->request->getMethod() == 'POST') {
			return 'JSONView';
		}
		return parent::getViewClass();
	}
}

