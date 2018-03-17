<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.validate.validator
 */

namespace Carrot3;

/**
 * Smartyバリデータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class SmartyValidator extends Validator {

	/**
	 * 初期化
	 *
	 * @access public
	 * @param string[] $params パラメータ配列
	 */
	public function initialize ($params = []) {
		$this['invalid_encoding_error'] = '正しいエンコードではありません。';
		return parent::initialize($params);
	}

	/**
	 * 実行
	 *
	 * @access public
	 * @param mixed $value バリデート対象
	 * @return bool 妥当な値ならばTrue
	 */
	public function execute ($value) {
		$tempfile = FileUtils::createTemporaryFile('.tpl', 'TemplateFile');
		if (is_array($value) && isset($value['is_file']) && !!$value['is_file']) {
			$file = new File($value['tmp_name']);
			if (!mb_check_encoding($file->getContents())) {
				$this->error = $this['invalid_encoding_error'];
				return false;
			}
			$tempfile->setContents($file->getContents());
		} else {
			$tempfile->setContents($value);
		}

		try {
			$smarty = new Smarty;
			$smarty->setTemplate($tempfile);
			$smarty->getContents();
		} catch (\Exception $e) {
			$this->error = $e->getMessage();
		}

		$tempfile->delete();
		return StringUtils::isBlank($this->error);
	}
}
