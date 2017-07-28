<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.validate.validator
 */

namespace Carrot3;

/**
 * 画像バリデータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ImageValidator extends Validator {
	private function getAllowedTypes () {
		if (StringUtils::isBlank($types = $this['types'])) {
			return Image::getTypes();
		} else {
			if (is_array($types) || ($types instanceof ParameterHolder)) {
				$types = Tuple::create($types);
			} else {
				$types = StringUtils::explode(',', $types);
			}

			foreach ($types as $type) {
				if ($suggested = MIMEType::getType($type)) {
					$type = $suggested;
				} else if (!mb_ereg('^image/', $type)) {
					$type = 'image/' . $type;
				}
				$types[] = $type;
			}
			return $types;
		}
	}

	/**
	 * 初期化
	 *
	 * @access public
	 * @param string[] $params パラメータ配列
	 */
	public function initialize ($params = []) {
		$this['types'] = 'jpg,gif,png';
		$this['types_error'] = '画像形式が正しくありません。';
		$this['min_height'] = null;
		$this['max_height'] = null;
		$this['min_width'] = null;
		$this['max_width'] = null;
		return parent::initialize($params);
	}

	/**
	 * 実行
	 *
	 * @access public
	 * @param mixed $value バリデート対象
	 * @return boolean 妥当な値ならばTrue
	 */
	public function execute ($value) {
		try {
			if (StringUtils::isBlank($name = $value['tmp_name'])) {
				throw new ImageException('ファイルが存在しない、又は正しくありません。');
			}
			$file = new ImageFile($name);
			$image = $file->getRenderer();
		} catch (\Exception $e) {
			$this->error = $this['types_error'];
			return false;
		}

		if (!$this->getAllowedTypes()->isContain($image->getType())) {
			$this->error = $this['types_error'];
		} else if ($this['min_width'] && ($image->getWidth() < $this['min_width'])) {
			$this->error = '幅が' . $this['min_width'] . 'ピクセルより不足しています。';
		} else if ($this['min_height'] && ($image->getHeight() < $this['min_height'])) {
			$this->error = '高さが' . $this['min_height'] . 'ピクセルより不足しています。';
		} else if ($this['max_width'] && ($this['max_width'] < $image->getWidth())) {
			$this->error = '幅が' . $this['max_width'] . 'ピクセルを超えています。';
		} else if ($this['max_height'] && ($this['max_height'] < $image->getHeight())) {
			$this->error = '高さが' . $this['max_height'] . 'ピクセルを超えています。';
		}
		return StringUtils::isBlank($this->error);
	}
}

