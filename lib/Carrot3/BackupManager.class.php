<?php
namespace Carrot3;

class BackupManager {
	use Singleton, BasicObject;
	protected $config;
	protected $temporaryDir;

	protected function __construct () {
		$this->config = Tuple::create();
		$this->temporaryDir = FileUtils::createTemporaryDirectory();
		$configure = ConfigManager::getInstance();
		foreach ($configure->compile('backup/application') as $key => $values) {
			$this->config[$key] = Tuple::create($values);
			$this->config[$key]->trim();
		}
	}

	public function __destruct () {
		$this->temporaryDir->delete();
	}

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
			LogManager::getInstance()->put('バックアップを実行しました。', $this);
			return $file;
		} catch (\Throwable $e) {
			$message = new StringFormat('バックアップに失敗しました。(%s)');
			$message[] = $e->getMessage();
			LogManager::getInstance()->put($message, $this);
		}
	}

	protected function createArchive ():ZipArchive {
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

	protected function getOptionalEntries ():Tuple {
		return Tuple::create();
	}

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
		(new SerializeHandler)->clear();
		RenderManager::getInstance()->clear();
		$this->restoreDatabase();
		$this->restoreDirectories();
		LogManager::getInstance()->put('リストアを実行しました。', $this);
	}

	protected function isValidBackup ():bool {
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

	public function isRestoreable ():bool {
		foreach ($this->config['databases'] as $name) {
			if (($db = Database::getInstance($name)) && !$db->isRestoreable()) {
				return false;
			}
		}
		return true;
	}
}
