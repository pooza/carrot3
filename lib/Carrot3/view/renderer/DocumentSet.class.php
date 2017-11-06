<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer
 */

namespace Carrot3;

/**
 * 書類セット
 *
 * ScriptSet/StyleSetの基底クラス
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class DocumentSet implements TextRenderer, HTTPRedirector, \IteratorAggregate {
	use HTTPRedirectorMethods, BasicObject;
	protected $name;
	protected $error;
	protected $type;
	protected $digest;
	protected $cacheFile;
	protected $documents;
	protected $contents;
	protected $url;
	static protected $entries;

	/**
	 * @access protected
	 * @param string $name 書類セット名
	 */
	public function __construct ($name) {
		$this->name = $name;
		$this->documents = Tuple::create();

		if (($entry = $this->getEntries()[$name]) && ($files = $entry['files'])) {
			foreach ($files as $file) {
				$this->register($file);
			}
		} else {
			if (!StringUtils::isBlank($this->getPrefix())) {
				$this->register($this->getPrefix());
			}
			$this->register($name);
		}
		$this->update();
	}

	/**
	 * 書類クラスを返す
	 *
	 * @access protected
	 * @return string 書類クラス
	 * @abstract
	 */
	abstract protected function getDocumentClass ();

	/**
	 * ディレクトリ名を返す
	 *
	 * @access protected
	 * @return string ディレクトリ名
	 * @abstract
	 */
	abstract protected function getDirectoryName ();

	/**
	 * ソースディレクトリを返す
	 *
	 * 書類クラスがファイルではないレンダラーなら、nullを返すように
	 *
	 * @access public
	 * @return Directory ソースディレクトリ
	 * @abstract
	 */
	public function getSourceDirectory () {
		return FileUtils::getDirectory($this->getDirectoryName());
	}

	/**
	 * キャッシュディレクトリを返す
	 *
	 * @access public
	 * @return Directory キャッシュディレクトリ
	 */
	public function getCacheDirectory () {
		$parent = FileUtils::getDirectory($this->getDirectoryName() . '_cache');
		if (!$dir = $parent->getEntry($this->name)) {
			$dir = $parent->createDirectory($this->name);
		}
		$dir->setDefaultSuffix($parent->getDefaultSuffix());
		return $dir;
	}

	/**
	 * キャッシュファイルを返す
	 *
	 * @access public
	 * @return File キャッシュファイル
	 */
	public function getCacheFile () {
		if (!$this->cacheFile) {
			$dir = $this->getCacheDirectory();
			if (!$this->cacheFile = $dir->getEntry($this->digest(), $this->getDocumentClass())) {
				$this->cacheFile = $dir->createEntry($this->digest(), $this->getDocumentClass());
			}
		}
		return $this->cacheFile;
	}

	/**
	 * 設定ファイルを返す
	 *
	 * @access protected
	 * @return Tuple 設定ファイルの配列
	 */
	protected function getConfigFiles () {
		$files = Tuple::create();
		$prefix = mb_ereg_replace(
			StringUtils::toLower(__NAMESPACE__) . '\\\\_?',
			'',
			StringUtils::underscorize(Utils::getShortClass($this))
		);
		foreach (['application', 'carrot'] as $name) {
			if ($file = ConfigManager::getConfigFile($prefix . '/' . $name)) {
				$files[] = $file;
			}
		}
		return $files;
	}

	/**
	 * 書類セット名を返す
	 *
	 * @access public
	 * @return string 書類セット名
	 */
	public function getName () {
		return $this->name;
	}

	/**
	 * 書類セットのプレフィックスを返す
	 *
	 * @access public
	 * @return string プレフィックス
	 */
	public function getPrefix () {
		$name = StringUtils::explode('.', $this->getName());
		if (1 < $name->count()) {
			return $name[0];
		}
	}

	/**
	 * ダイジェストを返す
	 *
	 * @access public
	 * @return string ダイジェスト
	 */
	public function digest () {
		if (!$this->digest) {
			$values = Tuple::create();
			$values['class'] = Utils::getClass($this);
			$values['name'] = $this->getName();
			foreach ($this as $entry) {
				$values[$entry->getPath()] = $entry->getUpdateDate()->getTimestamp();
			}
			$this->digest = Crypt::digest($values);
		}
		return $this->digest;
	}

	/**
	 * 登録
	 *
	 * @access public
	 * @param mixed $entry エントリー
	 */
	public function register ($entry) {
		if (is_string($entry)) {
			$dir = $this->getSourceDirectory();
			if ($file = $dir->getEntry($entry, $this->getDocumentClass())) {
				$entry = $file;
			}
		}
		if ($entry instanceof Serializable) {
			$this->documents[] = $entry;
		}
		$this->digest = null;
		$this->cacheFile = null;
		$this->contents = null;
	}

	/**
	 * 送信内容を返す
	 *
	 * @access public
	 * @return string 送信内容
	 */
	public function getContents () {
		return $this->contents;
	}

	/**
	 * 送信内容を更新
	 *
	 * @access public
	 */
	public function update () {
		$cache = $this->getCacheFile();
		if (StringUtils::isBlank($cache->getContents()) && !!$this->documents->count()) {
			$cache->getDirectory()->purge(Date::create());
			$contents = Tuple::create();
			foreach ($this as $file) {
				$file->serialize();
				$contents[] = $file->getSerialized();
			}
			$cache->setContents($contents->join("\n"));
			LogManager::getInstance()->put($this . 'を更新しました。', $this);
		}
		$this->contents = $cache->getContents();
	}

	/**
	 * 登録されている書類セットを配列で返す
	 *
	 * @access protected
	 * @return Tuple 登録内容
	 */
	protected function getEntries () {
		if (!self::$entries) {
			self::$entries = Tuple::create();
		}
		if (!self::$entries[Utils::getClass($this)]) {
			self::$entries[Utils::getClass($this)] = $entries = Tuple::create();
			foreach ($this->getSourceDirectory() as $file) {
				$entries[$file->getBaseName()] = Tuple::create();
			}
			foreach ($this->getConfigFiles() as $file) {
				foreach (ConfigManager::getInstance()->compile($file) as $key => $values) {
					$entries[$key] = Tuple::create($values);
				}
			}
			$entries->sort();
		}
		return self::$entries[Utils::getClass($this)];
	}

	/**
	 * 出力内容のサイズを返す
	 *
	 * @access public
	 * @return integer サイズ
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
		if (!$this->type) {
			$file = FileUtils::createTemporaryFile(null, $this->getDocumentClass());
			$this->type = $file->getType();
			$file->delete();
		}
		return $this->type;
	}

	/**
	 * エンコードを返す
	 *
	 * @access public
	 * @return string PHPのエンコード名
	 */
	public function getEncoding () {
		return 'utf-8';
	}

	/**
	 * 出力可能か？
	 *
	 * @access public
	 * @return boolean 出力可能ならTrue
	 */
	public function validate () {
		return StringUtils::isBlank($this->error);
	}

	/**
	 * エラーメッセージを返す
	 *
	 * @access public
	 * @return string エラーメッセージ
	 */
	public function getError () {
		return $this->error;
	}

	/**
	 * @access public
	 * @return Iterator イテレータ
	 */
	public function getIterator () {
		return new Iterator($this->documents);
	}

	/**
	 * リダイレクト対象
	 *
	 * @access public
	 * @return URL
	 */
	public function getURL () {
		if (!$this->url) {
			$this->url = FileUtils::createURL(
				$this->getDirectoryName() . '_cache',
				$this->getName() . '/' . $this->getCacheFile()->getName()
			);
		}
		return $this->url;
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('%s "%s"', Utils::getClass($this), $this->getName());
	}
}
