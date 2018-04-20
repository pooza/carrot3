<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage Console
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

namespace Carrot3\ConsoleModule;
use \Carrot3 as C;

require_once BS_LIB_DIR . '/Spyc.php';

class ConvertDictionariesAction extends C\Action {
	public function execute () {
		$dir = C\FileUtils::getDirectory('webapp')->getEntry('dictionaries');
		foreach ($dir as $file) {
			$words = C\Tuple::create();
			$dictionary = new C\DictionaryFile($file->getPath());
			foreach ($dictionary->getWords() as $key => $value) {
				if (mb_ereg('^(.*)_ja', $key, $matches)) {
					$words[trim($matches[1])] = ['ja' => trim($value)];
				}
			}
			$words->trim();
			$yaml = $dir->createEntry($dictionary->getBaseName() . '.yaml', 'ConfigFile');
			$yaml->setContents(\Spyc::YAMLDump($words->decode()));
			C\LogManager::getInstance()->put($file . '変換しました。', $yaml);
		}

		C\LogManager::getInstance()->put('実行しました。', $this);
		return C\View::NONE;
	}
}
