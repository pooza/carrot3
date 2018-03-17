<?php
/**
 * @package jp.co.b-shock.carrot3
 */

namespace Carrot3;

/**
 * コマンドラインビルダー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class CommandLine {
	use BasicObject;
	private $params;
	private $pipes;
	private $command;
	private $directory;
	private $result;
	private $returnCode = 0;
	private $background = false;
	private $stderrRedirectable = false;
	private $sleepSeconds = 0;
	const WITH_QUOTE = 1;

	/**
	 * @access public
	 * @param string $command prefix以降のコマンドパス。 'bin/mysql'等。
	 */
	public function __construct ($command) {
		if (StringUtils::isBlank($command)) {
			throw new Exception('コマンド名が空です。');
		}
		$this->command = $command;
		$this->params = Tuple::create();
		$this->pipes = Tuple::create();
	}

	/**
	 * ディレクトリプレフィックスを返す
	 *
	 * @access public
	 * @return Directory ディレクトリプレフィックス
	 */
	public function getDirectory () {
		return $this->directory;
	}

	/**
	 * ディレクトリプレフィックスを設定
	 *
	 * @access public
	 * @param Directory $dir ディレクトリプレフィックス
	 */
	public function setDirectory (Directory $dir) {
		if (!$dir->isExists()) {
			throw new Exception($dir . 'が存在しません。');
		}
		$this->directory = $dir;
	}

	/**
	 * 値を末尾に加える
	 *
	 * @access public
	 * @param string $value 値
	 * @param int $flags フラグのビット列
	 *   self::WITH_QUOTE クォートする
	 */
	public function push ($value, $flags = self::WITH_QUOTE) {
		if ($flags & self::WITH_QUOTE) {
			$value = self::quote($value);
		}
		$this->params[] = $value;
	}

	/**
	 * 値を末尾に加える
	 *
	 * pushのエイリアス
	 *
	 * @access public
	 * @param string $value 値
	 * @param int $flags フラグのビット列
	 *   self::WITH_QUOTE クォートする
	 * @final
	 */
	final public function addValue ($value, $flags = self::WITH_QUOTE) {
		$this->push($value, $flags);
	}

	/**
	 * パイプを加える
	 *
	 * @access public
	 * @param CommandLine $pipe パイプ
	 */
	public function registerPipe (CommandLine $pipe) {
		$this->pipes[] = $pipe;
	}

	/**
	 * 実行後の待機秒数を設定
	 *
	 * @access public
	 * @param int $seconds 秒数
	 */
	public function setSleepSeconds (int $seconds) {
		$this->sleepSeconds = $seconds;
	}

	/**
	 * 実行されたか？
	 *
	 * @access public
	 * @return bool 実行されたならTrue
	 */
	public function isExecuted () {
		return !!$this->result;
	}

	/**
	 * バックグラウンド実行か？
	 *
	 * @access public
	 * @return bool バックグラウンド実行ならTrue
	 */
	public function isBackground () {
		return $this->background;
	}

	/**
	 * バックグラウンド実行を設定
	 *
	 * @access public
	 * @param bool $mode バックグラウンド実行ならTrue
	 */
	public function setBackground (bool $mode = true) {
		$this->background = $mode;
	}

	/**
	 * stderrはリダイレクト可能か？
	 *
	 * @access public
	 * @return bool バックグラウンド実行ならTrue
	 */
	public function isStderrRedirectable () {
		return $this->stderrRedirectable;
	}

	/**
	 * stderrをリダイレクト可能に設定
	 *
	 * @access public
	 * @param bool $mode リダイレクト可能ならTrue
	 */
	public function setStderrRedirectable (bool $mode = true) {
		$this->stderrRedirectable = $mode;
	}

	/**
	 * コマンドは存在するか？
	 *
	 * @access public
	 * @return bool 存在するならTrue
	 */
	public function isExists () {
		if ($this->directory) {
			return !!$this->directory->getEntry($this->command);
		} else {
			$path = $this->controller->getAttribute('PATH');
			foreach (StringUtils::explode(PATH_SEPARATOR, $path) as $dir) {
				$dir = new Directory($dir);
				if (!!$dir->getEntry($this->command)) {
					return true;
				}
			}
			return false;
		}
	}

	/**
	 * コマンドを実行
	 *
	 * @access public
	 */
	public function execute () {
		exec($this->getContents(), $result, $this->returnCode);
		$this->result = Tuple::create($result);

		if ($seconds = $this->sleepSeconds) {
			sleep($seconds);
		}
	}

	/**
	 * コマンドラインを返す
	 *
	 * @access public
	 * @return string コマンドライン
	 */
	public function getContents () {
		$contents = clone $this->params;

		if ($this->directory) {
			if (!$file = $this->directory->getEntry($this->command)) {
				throw new Exception($this->command . 'が見つかりません。');
			}
			$contents->unshift($file->getPath());
		} else {
			$contents->unshift($this->command);
		}

		foreach ($this->pipes as $pipe) {
			$contents[] = '|';
			$contents[] = $pipe->getContents();
		}

		if ($this->isBackground()) {
			$contents[] = '> /dev/null &';
		} else if ($this->isStderrRedirectable()) {
			$contents[] = '2>&1';
		}

		return $contents->join(' ');
	}

	/**
	 * 実行後の標準出力を返す
	 *
	 * @access public
	 * @return string 標準出力
	 */
	public function getResult () {
		if (!$this->isExecuted()) {
			$this->execute();
		}
		return $this->result;
	}

	/**
	 * 実行後の戻り値を返す
	 *
	 * @access public
	 * @return int 戻り値
	 */
	public function getReturnCode () {
		if (!$this->isExecuted()) {
			$this->execute();
		}
		return $this->returnCode;
	}

	/**
	 * 実行後の戻り値は、エラーを含んでいたか？
	 *
	 * @access public
	 * @return bool エラーを含んでいたらTrue
	 */
	public function hasError () {
		return !!$this->getReturnCode();
	}

	/**
	 * 引数をクォートして返す
	 *
	 * @access public
	 * @param string $value 引数
	 * @return string クォートされた引数
	 * @static
	 */
	static private function quote ($value) {
		return escapeshellarg($value);
	}
}
