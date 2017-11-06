<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage test
 */

namespace Carrot3;

/**
 * テストマネージャ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class TestManager implements \IteratorAggregate {
	use Singleton;
	private $tests;
	private $errors;

	/**
	 * @access protected
	 */
	protected function __construct () {
		$this->tests = Tuple::create();
		$this->errors = Tuple::create();

		$entries = Tuple::create([
			FileUtils::getDirectory('tests'),
			FileUtils::getDirectory('local_tests'),
		]);
		foreach ($entries as $entry) {
			foreach ($entry as $dir) {
				if ($dir instanceof Directory) {
					$this->tests->merge($this->load($dir->getName(), $dir));
				}
			}
		}
	}

	private function load ($namespace, Directory $dir) {
		$tests = Tuple::create();
		foreach ($dir as $entry) {
			if (!$entry->isDotted()) {
				if ($entry instanceof Directory) {
					$tests->merge($this->load($namespace, $entry));
				} else if ($entry instanceof File) {
					require_once $entry->getPath();
					$class = $namespace . '\\' . Loader::extractClass($entry->getPath());
					$tests[] = new $class;
				}
			}
		}
		return $tests;
	}

	/**
	 * 実行
	 *
	 * @access public
	 * @param string $name テスト名
	 * @return boolean 成功ならTrue
	 */
	public function execute ($name = null) {
		foreach ($this as $test) {
			if (StringUtils::isBlank($name) || $test->isMatched($name)) {
				$message = new StringFormat('%s:');
				$message[] = Utils::getClass($test);
				$this->put($message);
				$test->execute();
				$this->errors->merge($test->getErrors());
			}
		}

		$this->put('===');
		$message = new StringFormat('%d errors');
		$message[] = $this->errors->count();
		$this->put($message);
		return !$this->errors->count();
	}

	/**
	 * 標準出力にメッセージを出力
	 *
	 * @access public
	 * @param mixed $message メッセージ
	 */
	public function put ($message) {
		if ($message instanceof StringFormat) {
			$message = $message->getContents();
		}
		print $message . "\n";
	}

	/**
	 * @access public
	 * @return Iterator イテレータ
	 */
	public function getIterator () {
		return $this->tests->getIterator();
	}
}
