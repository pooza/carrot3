<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage AdminLog
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

namespace Carrot3\AdminLogModule;
use \Carrot3 as C;

class BrowseAction extends C\Action {
	private $exception;

	public function getTitle () {
		return '管理ログ';
	}

	public function execute () {
		$this->request->setAttribute('dates', $this->getModule()->getDates());
		$entries = C\Tuple::create();
		$keyword = $this->request['key'];
		foreach ($this->getModule()->getEntries() as $entry) {
			if (C\StringUtils::isBlank($keyword)
				|| C\StringUtils::isContain($keyword, $entry['message'])
				|| ($keyword == $entry['remote_host'])
			) {
				$entries[] = $entry;
			}
		}
		$this->request->setAttribute('entries', $entries);
		return C\View::SUCCESS;
	}

	public function handleError () {
		$this->request->setAttribute('dates', []);
		$entry = [
			'exception' => true,
			'date' => C\Date::create()->format('Y-m-d H:i:s'),
			'remote_host' => $this->request->getHost()->getName(),
			'message' => 'ログを取得できません。',
		];
		if ($this->exception) {
			$message = new C\StringFormat('[%s] %s');
			$message[] = C\Utils::getClass($this->exception);
			$message[] = $this->exception->getMessage();
			$entry['message'] = $message->getContents();
		}
		$this->request->setAttribute('entries', [$entry]);
		return C\View::SUCCESS;
	}

	public function validate () {
		try {
			return !!$this->getModule()->getLogger();
		} catch (C\LogException $e) {
			$this->exception = $e;
			return false;
		}
	}
}
