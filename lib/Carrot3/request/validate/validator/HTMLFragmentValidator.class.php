<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.validate.validator
 */

namespace Carrot3;

/**
 * HTMLフラグメントバリデータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class HTMLFragmentValidator extends Validator {
	private $allowedTags;
	private $invalidNode;

	/**
	 * 実行
	 *
	 * @access public
	 * @param mixed $value バリデート対象
	 * @return bool 妥当な値ならばTrue
	 */
	public function execute ($value) {
		try {
			$command = $this->createCommand();
			$html = new StringFormat('<!DOCTYPE html><title>0</title><body>%s</body>');
			$html[] = str_replace("\n", ' ', $value);
			$command->addValue($html->getContents());
			$errors = Tuple::create();
			foreach ($command->getResult() as $line) {
				if (!mb_ereg('^line [0-9]+ column [0-9]+ - (.*)$', $line, $matches)) {
					continue;
				}
				if (!StringUtils::isBlank($message = $this->translateMessage($matches[1]))) {
					$errors[$message] = $message;
				}
			}
			if (!!$errors->count()) {
				$this->error = $errors->join();
				return false;
			}
		} catch (\Exception $e) {
			$this->error = $e->getMessage();
			return false;
		}
		return true;
	}

	private function createCommand () {
		$command = new CommandLine('echo');
		$tidy = new CommandLine('bin/tidy5');
		$tidy->setDirectory(FileUtils::getDirectory('tidy5'));
		$tidy->addValue('-errors');
		$command->registerPipe($tidy);
		$command->setStderrRedirectable();
		return $command;
	}

	private function translateMessage ($message) {
		$templates = [
			'^<([^>]+)> lacks "([^"]+)" attribute' => '<%s>タグには%s属性が必要です。',
			'^missing </([^>]+)>' => '<%s>タグが閉じられていません。',
			'^discarding unexpected <([^>]+)>' => '<%s>タグが予期せぬ場所に書かれています。',
			'^<([^>]+)> attribute "([^"]+)" not allowed' => '<%s>タグに%s属性を含めることはできません。',
			'^content occurs after end of body' => '<body>タグを閉じてはいけません。',
			'^trimming empty <([^>]+)>' => '<%s>タグの中身が空です。',
			'^<[^>]+> attribute "[^"]+" has invalid value' => null,
			'^<[^>]+> proprietary attribute "[^"]+"' => null,
			'^<[^>]+> illegal characters found in URI' => null,
		];
		$message = str_replace('Warning: ', '', $message);
		foreach ($templates as $pattern => $template) {
			if (mb_ereg($pattern, $message, $matches)) {
				if (StringUtils::isBlank($template)) {
					return;
				}
				$matches = Tuple::create($matches);
				$matches->shift();
				$format = new StringFormat($template);
				foreach ($matches as $match) {
					$format[] = $match;
				}
				return $format->getContents();
			}
		}
		return $message;
	}
}
