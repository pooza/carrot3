<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage log.logger
 */

namespace Carrot3;

/**
 * syslog用ロガー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class SystemLogger extends Logger {
	private $dates;
	private $entries;
	private $file;

	/**
	 * 初期化
	 *
	 * @access public
	 * @return bool
	 */
	public function initialize ():bool {
		return $this->createCommand()->isExists();
	}

	/**
	 * ログを出力
	 *
	 * @access public
	 * @param string $message ログメッセージ
	 * @param string $priority 優先順位
	 */
	public function put ($message, $priority) {
		$command = $this->createCommand();
		$line = Tuple::create();
		$line[] = '[server ' . $this->getServerHostName() . ']';
		$line[] = '[' . $priority . ']';
		$line[] = '[client ' . $this->getClientHostName() . ']';
		$line[] = $message;
		$command->push('-p');
		if ($this->isException($priority)) {
			$command->push('local6.error');
		} else {
			$command->push('local6.info');
		}
		$command->push($line->join(' '));
		$command->execute();
	}

	private function createCommand () {
		$command = new CommandLine('bin/logger');
		$command->setDirectory(FileUtils::getDirectory('logger'));
		$command->push('-t');
		$command->push('carrot-' . $this->getServerHostName());
		return $command;
	}

	/**
	 * ログディレクトリを返す
	 *
	 * @access public
	 * @return LogDirectory ログディレクトリ
	 */
	public function getDirectory () {
		return FileUtils::getDirectory('log');
	}

	/**
	 * 日付の配列を返す
	 *
	 * @access public
	 * @return Tuple 日付の配列
	 */
	public function getDates () {
		if (!$this->dates) {
			$this->dates = Tuple::create();
			foreach ($this->getDirectory() as $file) {
				if (!$date = Date::create($file->getBaseName())) {
					continue;
				}
				$this->dates[$date->format('Y-m-d')] = $date->format('Y-m-d(ww)');
			}
		}
		return $this->dates;
	}

	/**
	 * エントリーを抽出して返す
	 *
	 * @access public
	 * @param string Date 対象日付
	 * @return Tuple エントリーの配列
	 */
	public function getEntries (Date $date) {
		if (!$this->entries) {
			$this->entries = Tuple::create();
			if ($this->getDates()->hasParameter($date->format('Y-m-d'))) {
				$file = $this->getDirectory()->getEntry($date->format('Y-m-d'));
				$this->entries->setParameters($file->getEntries());
			}
		}
		return $this->entries;
	}
}
