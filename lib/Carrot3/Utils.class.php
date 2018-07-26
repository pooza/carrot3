<?php
namespace Carrot3;

class Utils {
	private function __construct () {
	}

	static public function isPathAbsolute ($path):bool {
		return !StringUtils::isBlank($path) && ($path[0] == '/');
	}

	static public function getUniqueID ():string {
		return Crypt::digest([
			Date::create()->format('YmdHis'),
			uniqid(Numeric::getRandom(), true),
		]);
	}

	static public function executeMethod ($object, $method, $values = []) {
		if (is_string($object)) {
			$object = Loader::getInstance()->getClass($object);
		}
		if (!method_exists($object, $method)) {
			$message = new StringFormat('クラス "%s" のメソッド "%s" が未定義です。');
			$message[] = Utils::getClass($object);
			$message[] = $method;
			throw new \BadFunctionCallException($message->getContents());
		}
		return call_user_func_array([$object, $method], $values);
	}

	static public function getNamespace ($class):string {
		try {
			return (new \ReflectionClass($class))->getNamespaceName();
		} catch (\Throwable $e) {
			return '';
		}
	}

	static public function getShortClass ($class):string {
		try {
			return (new \ReflectionClass($class))->getShortName();
		} catch (\Throwable $e) {
			return '';
		}
	}

	static public function getClass ($class):string {
		try {
			return (new \ReflectionClass($class))->getName();
		} catch (\Throwable $e) {
			return '';
		}
	}

	static public function getParentClasses ($class):iterable {
		try {
			$classes = [];
			$class = new \ReflectionClass($class);
			do {
				$classes[] = $class->getName();
			} while ($class = $class->getParentClass());
			return $classes;
		} catch (\Throwable $e) {
			return [];
		}
	}
}
