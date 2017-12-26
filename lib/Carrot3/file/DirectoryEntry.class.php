<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage file
 */

namespace Carrot3;

/**
 * ディレクトリエントリ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class DirectoryEntry {
	use BasicObject;
	protected $name;
	protected $path;
	protected $id;
	private $suffix;
	private $basename;
	private $shortPath;
	private $linkTarget;
	protected $directory;

	/**
	 * ユニークなファイルIDを返す
	 *
	 * @access public
	 * @return integer ID
	 */
	public function getID () {
		if (!$this->id) {
			$this->id = Crypt::digest([
				$this->getPath(),
				fileinode($this->getPath()),
			]);
		}
		return $this->id;
	}

	/**
	 * 名前を返す
	 *
	 * @access public
	 * @return string 名前
	 */
	public function getName () {
		if (!$this->name) {
			$this->name = basename($this->getPath());
		}
		return $this->name;
	}

	/**
	 * 名前を設定
	 *
	 * renameのエイリアス
	 *
	 * @access public
	 * @param string $name 新しい名前
	 * @final
	 */
	final public function setName ($name) {
		return $this->rename($name);
	}

	/**
	 * リネーム
	 *
	 * @access public
	 * @param string $name 新しい名前
	 */
	public function rename ($name) {
		if (!$this->isExists()) {
			throw new FileException($this . 'が存在しません。');
		} else if (StringUtils::isContain('/', $name)) {
			throw new FileException($this . 'をリネームできません。');
		}

		$path = $this->getDirectory()->getPath() . '/' . basename($name);

		$command = new CommandLine('mv');
		$command->setStderrRedirectable();
		$command->push($this->getPath());
		$command->push($path);
		if ($command->getReturnCode()) {
			throw new FileException($command->getResult());
		}

		$this->setPath($path);
		$this->getDirectory()->clearEntryNames();
	}

	/**
	 * 削除
	 *
	 * @access public
	 * @abstract
	 */
	abstract public function delete ();

	/**
	 * パスを返す
	 *
	 * @access public
	 * @return string パス
	 */
	public function getPath () {
		return $this->path;
	}

	/**
	 * パスを設定
	 *
	 * @access protected
	 * @param string $path パス
	 */
	protected function setPath ($path) {
		if (!Utils::isPathAbsolute($path) || StringUtils::isContain('../', $path)) {
			$message = new StringFormat('パス"%s"が正しくありません。');
			$message[] = $path;
			throw new FileException($message);
		}
		$this->path = $path;
		$this->name = null;
		$this->basename = null;
		$this->suffix = null;
	}

	/**
	 * 短いパスを返す
	 *
	 * @access public
	 * @return string 短いパス
	 */
	public function getShortPath () {
		if (!$this->shortPath) {
			$this->shortPath = str_replace(
				FileUtils::getPath('root') . '/',
				'',
				$this->getPath()
			);
		}
		return $this->shortPath;
	}

	/**
	 * 移動
	 *
	 * @access public
	 * @param Directory $dir 移動先ディレクトリ
	 */
	public function moveTo (Directory $dir) {
		if (!$this->isExists()) {
			throw new FileException($this . 'が存在しません。');
		}

		$path = $dir->getPath() . '/' . $this->getName();
		$command = new CommandLine('mv');
		$command->setStderrRedirectable();
		$command->push($this->getPath());
		$command->push($path);
		if ($command->getReturnCode()) {
			throw new FileException($command->getResult());
		}

		$this->setPath($path);
	}

	/**
	 * コピー
	 *
	 * @access public
	 * @param Directory $dir コピー先ディレクトリ
	 * @return File コピーされたファイル
	 */
	public function copyTo (Directory $dir) {
		$path = $dir->getPath() . '/' . $this->getName();
		if (!copy($this->getPath(), $path)) {
			throw new FileException($this . 'をコピーできません。');
		}
		$class = Utils::getClass($this);
		return new $class($path);
	}

	/**
	 * ドットファイル等を削除
	 *
	 * @access public
	 */
	public function clearDottedFiles () {
		if ($this->isDotted()) {
			$this->delete();
		}
	}

	/**
	 * サフィックスを返す
	 *
	 * @access public
	 * @return string サフィックス
	 */
	public function getSuffix () {
		if (!$this->suffix) {
			$this->suffix = FileUtils::getSuffix($this->getName());
		}
		return $this->suffix;
	}

	/**
	 * ベース名を返す
	 *
	 * @access public
	 * @return string ベース名
	 */
	public function getBaseName () {
		if (!$this->basename) {
			$this->basename = basename($this->getPath(), $this->getSuffix());
		}
		return $this->basename;
	}

	/**
	 * 名前がドットから始まるか？
	 *
	 * @access public
	 * @return boolean ドットから始まるならTrue
	 */
	public function isDotted () {
		return FileUtils::isDottedName($this->getName());
	}

	/**
	 * シンボリックリンクか？
	 *
	 * @access public
	 * @return boolean シンボリックリンクならTrue
	 */
	public function isLink () {
		return is_link($this->getPath());
	}

	/**
	 * リンク先を返す
	 *
	 * @access public
	 * @return DirectoryEntry リンク先
	 */
	public function getLinkTarget () {
		if ($this->isLink() && !$this->linkTarget) {
			$class = Utils::getClass($this);
			$this->linkTarget = new $class(readlink($this->getPath()));
		}
		return $this->linkTarget;
	}

	/**
	 * シンボリックリンクを作成
	 *
	 * @access public
	 * @param Directory $dir 作成先ディレクトリ
	 * @param string $name リンクのファイル名。空欄の場合は、元ファイルと同じ。
	 * @return DirectoryEntry リンク先
	 */
	public function createLink (Directory $dir, $name = null) {
		if (StringUtils::isBlank($name)) {
			$name = $this->getName();
		}

		$path = $dir->getPath() . '/' . $name;
		if (is_link($path)) {
			unlink($path);
		}
		symlink($this->getPath(), $path);
		return $dir->getEntry($name);
	}

	/**
	 * 親ディレクトリを返す
	 *
	 * @access public
	 * @return Directory ディレクトリ
	 */
	public function getDirectory () {
		if (!$this->directory) {
			$this->directory = new Directory(dirname($this->getPath()));
		}
		return $this->directory;
	}

	/**
	 * 作成日付を返す
	 *
	 * @access public
	 * @return Date 作成日付
	 */
	public function getCreateDate () {
		if (!$this->isExists()) {
			throw new FileException($this . 'が存在しません。');
		}
		return Date::create(filectime($this->getPath()), Date::TIMESTAMP);
	}

	/**
	 * 更新日付を返す
	 *
	 * @access public
	 * @return Date 更新日付
	 */
	public function getUpdateDate () {
		if (!$this->isExists()) {
			throw new FileException($this . 'が存在しません。');
		}
		return Date::create(filemtime($this->getPath()), Date::TIMESTAMP);
	}

	/**
	 * 存在するか？
	 *
	 * @access public
	 * @return boolean 存在するならtrue
	 */
	public function isExists () {
		return file_exists($this->getPath());
	}

	/**
	 * 存在し、かつ読めるか？
	 *
	 * @access public
	 * @return boolean 読めればtrue
	 */
	public function isReadable () {
		return is_readable($this->getPath());
	}

	/**
	 * 存在し、書き込めるか？
	 *
	 * @access public
	 * @return boolean 書き込めればtrue
	 */
	public function isWritable () {
		return is_writable($this->getPath());
	}

	/**
	 * ファイルモード（パーミッション）を設定
	 *
	 * @access public
	 * @param integer $mode ファイルモード
	 * @param integer $flags フラグのビット列
	 */
	public function setMode ($mode, $flags = 0) {
		if (!chmod($this->getPath(), $mode)) {
			throw new FileException($this . 'のファイルモードを変更できません。');
		}
	}
}
