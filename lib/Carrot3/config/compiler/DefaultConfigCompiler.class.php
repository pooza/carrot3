<?php
namespace Carrot3;

class DefaultConfigCompiler extends ConfigCompiler {
	public function execute (ConfigFile $file) {
		// $file->serialize()が使用できないケースがある為、直接シリアライズ
		$serials = new SerializeHandler;
		if ($serials->getAttribute($file, $file->getUpdateDate()) === null) {
			$serials->setAttribute($file, $this->getContents($file->getResult()));
		}

		$this->clearBody();
		$line = sprintf('return %s;', self::quote($serials[$file]));
		$this->putLine($line);
		return $this->getBody();
	}

	protected function getContents (?iterable $config) {
		return $config;
	}
}
