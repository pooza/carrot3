<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage file
 */

namespace Carrot3;

/**
 * ZIPアーカイブ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ZipArchive extends \ZipArchive implements Renderer {
	private $file;
	private $temporaryFile;
	private $opened = false;

	/**
	 * @access public
	 */
	public function __destruct () {
		if ($this->opened) {
			$this->close();
		}
	}

	/**
	 * 開く
	 *
	 * @access public
	 * @param mixed $file ファイル、又はそのパス。nullの場合は、一時ファイルを使用。
	 * @param mixed $flags フラグのビット列
	 *   self::OVERWRITE
	 *   self::CREATE
	 *   self::EXCL
	 *   self::CHECKCONS
	 * @return mixed 正常終了時はtrue、それ以外はエラーコード。
	 */
	public function open ($path = null, $flags = null) {
		if ($this->opened) {
			throw new FileException($this->getFile() . 'が開かれています。');
		}
		$this->setFile($path);
		$this->opened = true;
		return parent::open($this->getFile()->getPath(), self::OVERWRITE);
	}

	/**
	 * 閉じる
	 *
	 * @access public
	 * @return mixed 正常終了時はtrue、それ以外はエラーコード。
	 */
	public function close () {
		if ($this->opened) {
			$this->opened = false;
			return parent::close();
		}
	}

	/**
	 * 展開
	 *
	 * @access public
	 * @param mixed $path 展開先パス、又はディレクトリ
	 * @param mixed $entries 対象エントリー名、又はその配列。
	 * @return bool 正常終了時はtrue。
	 */
	public function extractTo ($path, $entries = null) {
		if ($path instanceof Directory) {
			$path = $path->getPath() . '/';
		}
		$command = new CommandLine('bin/unzip');
		$command->setDirectory(FileUtils::getDirectory('unzip'));
		$command->push($this->getFile()->getPath());
		$command->push('-d');
		$command->push($path);
		$command->execute();
		return true;
	}

	/**
	 * エントリーを登録
	 *
	 * @access public
	 * @param DirectoryEntry $entry エントリー
	 * @param string $prefix エントリー名のプレフィックス
	 * @param int $flags フラグのビット列
	 *   Directory::WITHOUT_DOTTED ドットファイルを除く
	 */
	public function register (DirectoryEntry $entry, $prefix = null, int $flags = 0) {
		if (($flags & Directory::WITHOUT_DOTTED) && $entry->isDotted()) {
			return;
		}

		if (StringUtils::isBlank($prefix)) {
			$path = $entry->getName();
		} else {
			$path = $prefix . '/' . $entry->getName();
		}
		if ($entry instanceof Directory) {
			$this->addEmptyDir($path);
			foreach ($entry as $node) {
				$this->register($node, $path, $flags);
			}
		} else {
			$this->addFile($entry->getPath(), $path);
		}
	}

	/**
	 * ファイルを返す
	 *
	 * @access public
	 * @return File ファイル
	 */
	public function getFile () {
		if (!$this->file) {
			$this->temporaryFile = true;
			$this->file = FileUtils::createTemporaryFile('.zip');
		}
		return $this->file;
	}

	/**
	 * ファイルを設定
	 *
	 * @access public
	 * @param mixed $file ファイル
	 */
	public function setFile ($file) {
		if ($this->opened) {
			throw new FileException($this->getFile() . 'が開かれています。');
		}
		if (StringUtils::isBlank($file)) {
			$file = null;
		} else if (!($file instanceof File)) {
			$path = $file;
			if (!Utils::isPathAbsolute($path)) {
				$path = FileUtils::getPath('tmp') . '/' . $path;
			}
			$this->temporaryFile = false;
			$file = new File($path);
		}
		$this->file = $file;
	}

	/**
	 * 出力内容を返す
	 *
	 * @access public
	 */
	public function getContents ():string {
		$this->close();
		return $this->getFile()->getContents();
	}

	/**
	 * 出力内容のサイズを返す
	 *
	 * @access public
	 * @return int サイズ
	 */
	public function getSize () {
		return strlen($this->getContents());
	}

	/**
	 * メディアタイプを返す
	 *
	 * @access public
	 * @return string メディアタイプ
	 */
	public function getType () {
		return MIMEType::getType('zip');
	}

	/**
	 * 出力可能か？
	 *
	 * @access public
	 * @return bool 出力可能ならTrue
	 */
	public function validate ():bool {
		return true;
	}

	/**
	 * エラーメッセージを返す
	 *
	 * @access public
	 * @return string エラーメッセージ
	 */
	public function getError () {
		return null;
	}
}
