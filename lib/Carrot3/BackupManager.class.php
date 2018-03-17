<?php
/**
 * @package jp.co.b-shock.carrot3
 */

namespace Carrot3;

/**
 * バックアップマネージャ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class BackupManager {
	use Singleton, BasicObject;
	protected $config;
	protected $temporaryDir;

	/**
	 * @access protected
	 */
	protected function __construct () {
		$this->config = Tuple::create();
		$this->temporaryDir = FileUtils::createTemporaryDirectory();
		$configure = ConfigManager::getInstance();
		foreach ($configure->compile('backup/application') as $key => $values) {
			$this->config[$key] = Tuple::create($values);
			$this->config[$key]->trim();
		}
	}

	/**
	 * @access public
	 */
	public function __destruct () {
		$this->temporaryDir->delete();
	}

	/**
	 * ZIPアーカイブにバックアップを取り、返す
	 *
	 * @access public
	 * @param Directory $dir 出力先ディレクトリ
	 * @return File バックアップファイル
	 */
	public function execute (Directory $dir = null) {
		if (!$dir) {
			$dir = FileUtils::getDirectory('backup');
		}

		$name = new StringFormat('%s_%s.zip');
		$name[] = $this->controller->getHost()->getName();
		$name[] = Date::create()->format('Y-m-d');

		try {
			$file = $this->createArchive()->getFile();
			$file->rename($name->getContents());
			$file->moveTo($dir);
			$dir->purge();
		} catch (\Exception $e) {
			return;
		}

		LogManager::getInstance()->put('バックアップを実行しました。', $this);
		return $file;
	}

	protected function createArchive () {
		$zip = new ZipArchive;
		$zip->open();
		foreach ($this->config['databases'] as $name) {
			if ($db = Database::getInstance($name)) {
				$zip->register($db->getBackupTarget());
			}
		}
		foreach ($this->config['directories'] as $name) {
			if ($dir = FileUtils::getDirectory($name)) {
				$zip->register($dir);
			}
		}
		foreach ($this->getOptionalEntries() as $entry) {
			$zip->register($entry);
		}
		$zip->close();
		return $zip;
	}

	protected function getOptionalEntries () {
		return Tuple::create();
	}

	/**
	 * ZIPアーカイブファイルをリストア
	 *
	 * @access public
	 * @param File $file アーカイブファイル
	 */
	public function restore (File $file) {
		if (!$this->isRestoreable()) {
			throw new FileException('この環境はリストアできません。');
		}

		$zip = new ZipArchive;
		$zip->open($file->getPath());
		$zip->extractTo($this->temporaryDir);
		$zip->close();

		if (!$this->isValidBackup()) {
			throw new FileException('このバックアップからはリストアできません。');
		}

		(new ImageManager)->clear();
		RenderManager::getInstance()->clear();
		foreach (SerializeHandler::getClasses() as $class) {
			foreach (TableHandler::create($class) as $record) {
				$record->removeSerialized();
			}
		}
		$this->restoreDatabase();
		$this->restoreDirectories();
		$this->restoreOptional();
		LogManager::getInstance()->put('リストアを実行しました。', $this);
	}

	protected function isValidBackup () {
		foreach ($this->config['databases'] as $name) {
			if (!$this->temporaryDir->getEntry($name . '.sqlite3')) {
				return false;
			}
		}
		foreach ($this->config['directories'] as $name) {
			if (!$this->temporaryDir->getEntry($name)) {
				return false;
			}
		}
		return true;
	}

	protected function restoreDatabase () {
		foreach ($this->config['databases'] as $name) {
			$file = $this->temporaryDir->getEntry($name . '.sqlite3');
			$file->moveTo(FileUtils::getDirectory('db'));
			Database::getInstance($name, Database::RECONNECT);
		}
	}

	protected function restoreDirectories () {
		foreach ($this->config['directories'] as $name) {
			$dest = FileUtils::getDirectory($name);
			$dest->clear();
			foreach ($this->temporaryDir->getEntry($name) as $file) {
				if (!$file->isDotted()) {
					$file->moveTo($dest);
				}
			}
		}
	}

	protected function restoreOptional () {
		// 適宜オーバーライド
	}

	/**
	 * リストア可能な環境か？
	 *
	 * @access public
	 * @return bool リストアに対応した環境ならTrue
	 */
	public function isRestoreable () {
		foreach ($this->config['databases'] as $name) {
			if (($db = Database::getInstance($name)) && !$db->isRestoreable()) {
				return false;
			}
		}
		return true;
	}
}
