<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage media.image
 */

namespace Carrot3;

/**
 * 座標
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class Coordinate {
	private $image;
	private $x;
	private $y;

	/**
	 * @access public
	 * @param Image $image 画像レンダラー
	 * @param integer $x X座標
	 * @param integer $y Y座標
	 */
	public function __construct (Image $image, $x, $y) {
		$this->image = $image;
		$this->x = $x;
		$this->y = $y;
		$this->validate();
	}

	/**
	 * X座標を返す
	 *
	 * @access public
	 * @return integer X座標
	 */
	public function getX () {
		return $this->x;
	}

	/**
	 * Y座標を返す
	 *
	 * @access public
	 * @return integer Y座標
	 */
	public function getY () {
		return $this->y;
	}

	private function validate () {
		if (($this->getX() < 0) || ($this->getImage()->getWidth() - 1 < $this->getX())) {
			$message = new StringFormat('X座標 "%d" は領域外です。');
			$message[] = $this->getX();
			throw new ImageException($message);
		} else if (($this->getY() < 0) || ($this->getImage()->getHeight() - 1 < $this->getY())) {
			$message = new StringFormat('Y座標 "%d" は領域外です。');
			$message[] = $this->getY();
			throw new ImageException($message);
		}
	}

	/**
	 * 移動
	 *
	 * @access public
	 * @param integer $x X座標
	 * @param integer $y Y座標
	 * @return Coordinate 移動後の自分自身
	 */
	public function move ($x, $y) {
		$this->x += $x;
		$this->y += $y;
		$this->validate();
		return $this;
	}

	/**
	 * 回転
	 *
	 * @access public
	 * @param Coordinate $origin 中心
	 * @param integer $angle 角度
	 * @return Coordinate 移動後の自分自身
	 */
	public function rotate (Coordinate $origin, $angle) {
		$x = $this->getX() - $origin->getX();
		$y = $this->getY() - $origin->getY();
		$sin = sin(deg2rad($angle));
		$cos = cos(deg2rad($angle));
		return $this->move(
			($x * $cos - $y * $sin) - $x,
			($x * $sin + $y * $cos) - $y
		);
	}

	/**
	 * 画像レンダラーを返す
	 *
	 * @access public
	 * @return Image 画像レンダラー
	 */
	public function getImage () {
		return $this->image;
	}
}

