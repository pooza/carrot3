<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage image.attachment
 */

namespace Carrot3;

/**
 * 添付ファイルのダウンロードアクション
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class AttachmentAction extends RecordAction {
	public function execute () {
		$this->request->setAttribute(
			'filename',
			$this->getRecord()->getAttachmentFileName($this->request['name'])
		);
		$this->request->setAttribute(
			'renderer',
			$this->getRecord()->getAttachment($this->request['name'])
		);
		return View::SUCCESS;
	}

	public function handleError () {
		return $this->controller->getAction('not_found')->forward();
	}

	public function validate ():bool {
		return (parent::validate()
			&& ($this->getRecord() instanceof AttachmentContainer)
			&& $this->getRecord()->getAttachment($this->request['name'])
		);
	}
}
