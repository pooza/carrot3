<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.validate.validator
 */

namespace Carrot3;

/**
 * URLバリデータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class URLValidator extends Validator {

	/**
	 * 初期化
	 *
	 * @access public
	 * @param iterable $params パラメータ配列
	 * @return bool
	 */
	public function initialize (?iterable $params = []):bool {
		$this['net_error'] = '正しくありません。';
		$this['schemes'] = ['http', 'https'];
		$this['scheme_error'] = sprintf(
			'スキーム(%s)が正しくありません。',
			join('|', $this['schemes'])
		);
		$this['allow_fullpath'] = false;
		return Validator::initialize($params);
	}

	private function getSchemes () {
		return Tuple::create($this['schemes']);
	}

	/**
	 * 実行
	 *
	 * @access public
	 * @param mixed $value バリデート対象
	 * @return bool 妥当な値ならばTrue
	 */
	public function execute ($value) {
		try {
			$pattern = 'https?://[-_.!~*\'()a-zA-Z0-9;/?:@&=+$,%#]+';
			if (!$this['allow_fullpath'] && !mb_ereg($pattern, $value)) {
				$this->error = $this['net_error'];
			}
			if (!$url = URL::create($value)) {
				$this->error = $this['net_error'];
			}
			if (!$this['allow_fullpath'] && !$this->getSchemes()->isContain($url['scheme'])) {
				$this->error = $this['scheme_error'];
			}
		} catch (\Throwable $e) {
			$this->error = $this['net_error'];
		}
		return StringUtils::isBlank($this->error);
	}
}
