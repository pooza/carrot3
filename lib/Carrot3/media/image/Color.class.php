<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage media.image
 */

namespace Carrot3;

/**
 * 色
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class Color extends ParameterHolder {
	const DEFAULT_COLOR = 'black';

	/**
	 * @access public
	 * @param string $color HTML形式の色コード
	 */
	public function __construct ($color = null) {
		if (StringUtils::isBlank($color)) {
			$color = self::DEFAULT_COLOR;
		}
		$this->setColor($color);
	}

	/**
	 * HTML形式の色コードを設定
	 *
	 * @access public
	 * @param string $color HTML形式の色コード
	 */
	public function setColor ($color) {
		$color = ltrim($color, '#');
		if (StringUtils::isBlank($color) || Numeric::isZero($color)) {
			$this['red'] = 0;
			$this['green'] = 0;
			$this['blue'] = 0;
		} else if (mb_ereg('^[[:xdigit:]]{6}$', $color)) {
			$this['red'] = hexdec($color[0] . $color[1]);
			$this['green'] = hexdec($color[2] . $color[3]);
			$this['blue'] = hexdec($color[4] . $color[5]);
		} else if (mb_ereg('^[[:xdigit:]]{3}$', $color)) {
			$this['red'] = hexdec($color[0] . $color[0]);
			$this['green'] = hexdec($color[1] . $color[1]);
			$this['blue'] = hexdec($color[2] . $color[2]);
		} else {
			$color = StringUtils::toLower($color);
			$colors = Tuple::create(ConfigManager::getInstance()->compile('color'));
			if (StringUtils::isBlank($code = $colors[$color])) {
				$message = new StringFormat('色 "%s" は正しくありません。');
				$message[] = $color;
				throw new ImageException($message);
			}
			$this->setColor($code);
		}
	}

	/**
	 * HTML形式の色コードを返す
	 *
	 * "#" をつける。
	 *
	 * @access public
	 * @return string HTML形式の色コード
	 */
	public function getContents ():string {
		return '#' . $this->getCode();
	}

	/**
	 * HTML形式の色コードを返す
	 *
	 * "#" をつけない。
	 *
	 * @access public
	 * @return string HTML形式の色コード
	 */
	public function getCode () {
		return sprintf('%02x%02x%02x', $this['red'], $this['green'], $this['blue']);
	}
}
