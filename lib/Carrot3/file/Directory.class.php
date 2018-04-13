<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage file
 */

namespace Carrot3;

/**
 * ディレクトリ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class Directory extends DirectoryEntry implements \IteratorAggregate {
	private $suffix;
	private $entries;
	private $url;
	private $zip;
	const SORT_ASC = 'asc';
	const SORT_DESC = 'dsc';
	const WITHOUT_DOTTED = 1;
	const WITH_RECURSIVE = 2;

	/**
	 * @access public
	 * @param string $path ディレクトリのパス
	 */
	public function __construct ($path) {
		$this->setPath($path);
		if (!is_dir($this->getPath())) {
			throw new FileException($this . 'を開くことができません。');
		}
	}

	/**
	 * パスを設定
	 *
	 * @access protected
	 * @param string $path パス
	 */
	protected function setPath ($path) {
		parent::setPath(rtrim($path, '/'));
	}

	/**
	 * 規定サフィックスを返す
	 *
	 * @access public
	 * @return string サフィックス
	 */
	public function getDefaultSuffix () {
		return $this->suffix;
	}

	/**
	 * 規定サフィックスを設定
	 *
	 * @access public
	 * @param string $suffix
	 */
	public function setDefaultSuffix ($suffix) {
		$this->suffix = ltrim($suffix, '*');
		$this->entries = null;
	}

	/**
	 * エントリーの名前を返す
	 *
	 * 拡張子による抽出を行い、かつ拡張子を削除する。
	 *
	 * @access public
	 * @param int $flags フラグのビット列
	 *   self::WITHOUT_DOTTED ドットファイルを除く
	 * @return Tuple 抽出されたエントリー名
	 */
	public function getEntryNames (int $flags = 0) {
		$names = Tuple::create();
		foreach ($this->getAllEntryNames() as $name) {
			if (($flags & self::WITHOUT_DOTTED) && FileUtils::isDottedName($name)) {
				continue;
			}
			if (fnmatch('*' . $this->getDefaultSuffix(), $name)) {
				$names[] = basename($name, $this->getDefaultSuffix());
			}
		}
		return $names;
	}

	/**
	 * 全エントリーの名前を返す
	 *
	 * 拡張子に関わらず全てのエントリーを返す。
	 *
	 * @access public
	 * @return Tuple 全エントリー名
	 */
	public function getAllEntryNames () {
		if (!$this->entries) {
			$this->entries = Tuple::create();
			$iterator = new \DirectoryIterator($this->getPath());
			foreach ($iterator as $entry) {
				if (!$entry->isDot()) {
					$this->entries[] = $entry->getFilename();
				}
			}
			if ($this->getSortOrder() == self::SORT_DESC) {
				$this->entries->sort(Tuple::SORT_VALUE_DESC);
			} else {
				$this->entries->sort(Tuple::SORT_VALUE_ASC);
			}
		}
		return $this->entries;
	}

	/**
	 * エントリー名キャッシュをクリア
	 *
	 * @access public
	 */
	public function clearEntryNames () {
		$this->entries = null;
	}

	/**
	 * エントリーを返す
	 *
	 * @access public
	 * @param string $name エントリーの名前
	 * @param string $class エントリーのクラス名
	 * @return DirectoryEntry ディレクトリかファイル
	 */
	public function getEntry (string $name, $class = null) {
		if (StringUtils::isBlank($class)) {
			$class = $this->getDefaultEntryClass();
		}
		$class = $this->loader->getClass($class);

		$path = $this->getPath() . '/' . StringUtils::stripControlCharacters($name);
		if ($this->hasSubDirectory() && is_dir($path)) {
			return new Directory($path);
		}
		foreach ([$path, $path . $this->getDefaultSuffix()] as $path) {
			if (is_file($path) || is_link($path)) {
				return new $class($path);
			}
		}
	}

	/**
	 * 新しく作ったエントリーを作って返す
	 *
	 * @access public
	 * @param string $name エントリーの名前
	 * @param string $class クラス名
	 * @return File ファイル
	 */
	public function createEntry (string $name, $class = null) {
		if (StringUtils::isBlank($class)) {
			$class = $this->getDefaultEntryClass();
		}

		$name = basename($name, $this->getDefaultSuffix());
		$path = $this->getPath() . '/' . $name . $this->getDefaultSuffix();

		$class = $this->loader->getClass($class);
		$file = new $class($path);
		$file->setContents(null);
		$this->entries = null;
		return $file;
	}

	/**
	 * コピー
	 *
	 * 再帰的にコピーを行う。ドットファイル等は対象に含まない。
	 *
	 * @access public
	 * @param Directory $dir コピー先ディレクトリ
	 * @return File コピーされたファイル
	 */
	public function copyTo (Directory $dir) {
		$name = $this->getName();
		if ($dir->getPath() == $this->getDirectory()->getPath()) {
			while ($dir->getEntry($name)) {
				$name = StringUtils::increment($name);
			}
		}
		if (!$dest = $dir->getEntry($name)) {
			$dest = $dir->createDirectory($name);
		}
		foreach ($this as $entry) {
			$entry->copyTo($dest);
		}
		return $dest;
	}

	/**
	 * 削除
	 *
	 * @access public
	 */
	public function delete () {
		if ($this->isLink()) {
			if (!unlink($this->getPath())) {
				throw new FileException($this . 'を削除できません。');
			}
		} else {
			$this->clear();
			if (!rmdir($this->getPath())) {
				throw new FileException($this . 'を削除できません。');
			}
		}
	}

	/**
	 * 全てのエントリを削除
	 *
	 * @access public
	 */
	public function clear () {
		$iterator = new \DirectoryIterator($this->getPath());
		foreach ($iterator as $entry) {
			if (FileUtils::isDottedName($entry->getFileName())) {
				continue;
			}
			$this->getEntry($entry->getFileName())->delete();
		}
	}

	/**
	 * ドットファイル等を削除
	 *
	 * @access public
	 */
	public function clearDottedFiles () {
		foreach ($this as $entry) {
			$entry->clearDottedFiles();
		}
		parent::clearDottedFiles();
		$this->entries = null;
	}

	/**
	 * 古いファイルを削除
	 *
	 * @access public
	 * @param Date $date 基準日
	 */
	public function purge (Date $date = null) {
		if (!$date) {
			$date = Date::create()->setParameter('month', '-1');
		}
		foreach ($this as $entry) {
			if ($entry->isDotted()) {
				continue;
			}
			if ($entry->getUpdateDate()->isPast($date)) {
				$entry->delete();
			}
		}

		$message = new StringFormat('%s内の、%s以前のエントリーを削除しました。');
		$message[] = $this;
		$message[] = $date->format('Y/n/j');
		LogManager::getInstance()->put($message, $this);
	}

	/**
	 * 新規ディレクトリを作り、返す
	 *
	 * @access public
	 * @param string $name ディレクトリの名前
	 * @param string $class クラス名
	 * @return Directory 作成されたディレクトリ
	 */
	public function createDirectory (string $name, $class = 'Carrot3\\Directory') {
		$path = $this->getPath() . '/' . $name;
		if (file_exists($path)) {
			if (!is_dir($path)) {
				throw new FileException($path . 'と同名のファイルが存在します。');
			}
		} else {
			mkdir($path);
		}
		$class = $this->loader->getClass($class);
		return new $class($path);
	}

	/**
	 * URLを返す
	 *
	 * FileUtils::createURLから呼ばれるので、こちらを利用すること。
	 *
	 * @access public
	 * @return HTTPURL URL
	 */
	public function getURL () {
		if (!$this->url) {
			$documentRoot = FileUtils::getPath('www');
			if (mb_ereg('^' . $documentRoot, $this->getPath())) {
				$this->url = URL::create();
				$this->url['path'] = str_replace($documentRoot, '', $this->getPath()) . '/';
			}
		}
		return $this->url;
	}

	/**
	 * ZIPアーカイブを返す
	 *
	 * @access public
	 * @param int $flags フラグのビット列
	 *   self::WITHOUT_DOTTED ドットファイルを除く
	 * @return ZipArchive ZIPアーカイブ
	 */
	public function getArchive ($flags = self::WITHOUT_DOTTED) {
		if (!extension_loaded('zip')) {
			throw new FileException('zipモジュールがロードされていません。');
		}
		if (!$this->zip) {
			$this->zip = new ZipArchive;
			$this->zip->open(null, \ZipArchive::OVERWRITE);
			foreach ($this as $entry) {
				$this->zip->register($entry, null, $flags);
			}
			$this->zip->close();
		}
		return $this->zip;
	}

	/**
	 * ファイルモード（パーミッション）を設定
	 *
	 * @access public
	 * @param int $mode ファイルモード
	 * @param int $flags フラグのビット列
	 *   self::WITH_RECURSIVE 再帰的に
	 */
	public function setMode (int $mode, int $flags = 0) {
		parent::setMode($mode);
		if ($flags & self::WITH_RECURSIVE) {
			foreach ($this as $entry) {
				$entry->setMode($mode, $flags);
			}
		}
	}

	/**
	 * @access public
	 * @return DirectoryIterator イテレータ
	 */
	public function getIterator () {
		return new DirectoryIterator($this);
	}

	/**
	 * サブディレクトリを持つか？
	 *
	 * @access public
	 * @return bool サブディレクトリを持つならTrue
	 */
	public function hasSubDirectory ():bool {
		return true;
	}

	/**
	 * エントリーのクラス名を返す
	 *
	 * @access public
	 * @return string エントリーのクラス名
	 */
	public function getDefaultEntryClass () {
		return $this->loader->getClass('File');
	}

	/**
	 * ソート順を返す
	 *
	 * @access public
	 * @return string (ソート順 self::SORT_ASC | self::SORT_DESC)
	 */
	public function getSortOrder () {
		return self::SORT_ASC;
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('ディレクトリ "%s"', $this->getShortPath());
	}
}
