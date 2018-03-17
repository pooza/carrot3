<?php
/**
 * @package jp.co.b-shock.carrot3
 */

namespace Carrot3;

/**
 * ジオコードエントリー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class Geocode extends ParameterHolder {
	private $stations;

	/**
	 * @access public
	 * @param mixed $params 要素の配列
	 */
	public function __construct ($params = []) {
		$this->setParameters($params);
	}

	/**
	 * パラメータを設定
	 *
	 * @access public
	 * @param string $name パラメータ名
	 * @param mixed $value 値
	 */
	public function setParameter ($name, $value) {
		if ($name == 'lon') {
			$name = 'lng';
		}
		parent::setParameter($name, $value);
	}

	/**
	 * 書式化して返す
	 *
	 * @access public
	 * @param string $separator 区切り文字
	 * @return string 書式化した文字列
	 */
	public function format ($separator = ',') {
		return $this['lat'] . $separator . $this['lng'];
	}

	/**
	 * script要素を返す
	 *
	 * @access public
	 * @param ParameterHolder $params パラメータ配列
	 * @return DivisionElement
	 */
	public function createElement (ParameterHolder $params) {
		$params = Tuple::create($params);
		$container = new DivisionElement;
		$container->setStyle('text-align', $params['align']);
		$inner = $container->addElement(new DivisionElement);
		$script = $container->addElement(new ScriptElement);

		if (StringUtils::isBlank($id = $params['container_id'])) {
			$id = 'map_' . Crypt::digest($params['address']);
		}
		$inner->setID($id);
		$inner->setStyle('width', $params['width']);
		$inner->setStyle('height', $params['height']);
		$inner->setStyle('display', 'inline-block');
		$inner->setBody('Loading...');

		$statement = new StringFormat('%s(document.getElementById(%s), %f, %f, %d);');
		$statement[] = BS_GEOCODE_MAP_FUNCTION;
		$statement[] = (new JSONSerializer)->encode($inner->getID());
		$statement[] = $this['lat'];
		$statement[] = $this['lng'];
		$statement[] = $params['zoom'];
		$script->setBody($statement);
		return $container;
	}

	/**
	 * 最寄り駅を返す
	 *
	 * @access public
	 * @param int $flags フラグのビット列
	 *   HeartRailsExpressService::FORCE_QUERY 新規取得を強制
	 * @return Tuple 最寄り駅
	 */
	public function getStations (int $flags = 0) {
		if (!$this->stations) {
			$this->stations = Tuple::create();
			try {
				$service = new HeartRailsExpressService;
				$this->stations->setParameters($service->getStations($this, $flags));
			} catch (\Exception $e) {
			}
		}
		return $this->stations;
	}
}
