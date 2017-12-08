<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage log
 */

namespace Carrot3;

/**
 * ログマネージャ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class LogManager implements \IteratorAggregate {
	use Singleton, BasicObject;
	private $loggers;

	/**
	 * @access protected
	 */
	protected function __construct () {
		$this->loggers = Tuple::create();
		foreach (Tuple::create(BS_LOG_LOGGERS) as $class) {
			$this->register($this->loader->createObject($class . 'Logger'));
		}
	}

	/**
	 * ロガーを登録
	 *
	 * @access public
	 * @param Logger $logger ロガー
	 */
	public function register (Logger $logger) {
		if ($logger->initialize()) {
			$this->loggers[] = $logger;
		}
	}

	/**
	 * 最優先のロガーを返す
	 *
	 * @access public
	 * @param Logger $logger ロガー
	 */
	public function getPrimaryLogger () {
		return $this->getIterator()->getFirst();
	}

	/**
	 * ログを出力
	 *
	 * @access public
	 * @param mixed $message ログメッセージ又は例外
	 * @param string $priority 優先順位
	 */
	public function put ($message, $priority = Logger::DEFAULT_PRIORITY) {
		if ($message instanceof StringFormat) {
			$message = $message->getContents();
		} else if ($message instanceof Exception) {
			$priority = $message->getName();
			$message = $message->getMessage();
		}
		if (is_object($priority)) {
			$priority = Utils::getClass($priority);
		}
		foreach ($this as $logger) {
			$logger->put($message, $priority);
		}
	}

	/**
	 * @access public
	 * @return Iterator イテレータ
	 */
	public function getIterator () {
		return $this->loggers->getIterator();
	}
}
