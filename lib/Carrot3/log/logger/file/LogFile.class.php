<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage log.logger.file
 */

namespace Carrot3;

/**
 * ログファイル
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class LogFile extends File {
	private $entries = [];

	/**
	 * バイナリファイルか？
	 *
	 * @access public
	 * @return bool バイナリファイルならTrue
	 */
	public function isBinary () {
		return false;
	}

	/**
	 * ログの内容を返す
	 *
	 * @access public
	 * @return string[][] ログの内容
	 */
	public function getEntries () {
		if (!$this->entries) {
			foreach ($this->getLines() as $line) {
				if (!mb_ereg('([0-9]{1,3}\.){3}[0-9]{1,3}', $line, $matches)) {
					continue;
				}
				$remoteaddr = $matches[0];
				$line = mb_ereg_replace('\[(client|server) [^\]]+\] ', null, $line);
				$fields = Tuple::create(mb_split('\s+', $line));
				$date = Date::create($fields[0]);
				$fields->removeParameter(0);
				$fields->removeParameter(1);
				$fields->removeParameter(2);
				$this->entries[] = [
					'date' => $date->format(),
					'remote_host' => (new Host($remoteaddr))->resolveReverse(),
					'exception' => mb_ereg('Exception', $line),
					'message' => $fields->join(' '),
				];
			}
		}
		return $this->entries;
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('ログファイル "%s"', $this->getShortPath());
	}
}
