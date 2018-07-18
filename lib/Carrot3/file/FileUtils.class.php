<?php
namespace Carrot3;

class FileUtils {
	private function __construct () {
	}

	static public function getDirectory (string $name):?Directory {
		return DirectoryLayout::getInstance()[$name];
	}

	static public function getPath (string $name) {
		if ($dir = self::getDirectory($name)) {
			return $dir->getPath();
		}
	}

	static public function createURL (string $name, $path = ''):?HTTPURL {
		if (self::getDirectory($name)) {
			$url = DirectoryLayout::getInstance()->createURL($name);
			$url['path'] .= $path;
			return $url;
		}
	}

	static public function getSuffix (string $name):string {
		$parts = StringUtils::explode('.', $name);
		if (1 < $parts->count()) {
			return StringUtils::toLower('.' . $parts->getIterator()->getLast());
		}
	}

	static public function getDefaultType (string $name) {
		return MIMEType::getType($name);
	}

	static public function isDottedName (string $name):bool {
		return mb_ereg('^\\.', basename($name));
	}

	static public function createTemporaryFile ($suffix = null, $class = 'Carrot3\\File') {
		if (StringUtils::isBlank($suffix)) {
			$name = Utils::getUniqueID();
		} else {
			$name = Utils::getUniqueID() . '.' . ltrim($suffix, '.');
		}
		if (!$file = FileUtils::getDirectory('tmp')->createEntry($name, $class)) {
			throw new FileException('一時ファイルが生成できません。');
		}
		return $file;
	}

	static public function createTemporaryDirectory ($class = 'Carrot3\\Directory') {
		$name = Utils::getUniqueID();
		if (!$dir = FileUtils::getDirectory('tmp')->createDirectory($name, $class)) {
			throw new FileException('一時ディレクトリが生成できません。');
		}
		return $dir;
	}
}
