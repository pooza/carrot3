<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage AdminUtility
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

namespace Carrot3\AdminUtilityModule;
use \Carrot3 as C;

class GeocodeAction extends C\Action {
	public function execute () {
		$maps = new C\GoogleMapsService;
		if (!$geocode = $maps->getGeocode($this->request['addr'])) {
			return C\View::ERROR;
		}

		$json = new ResultC\JSONRenderer;
		$json->setContents(C\Tuple::create([
			'lat' => $geocode['lat'],
			'lng' => $geocode['lng'],
		]));
		$this->request->setAttribute('renderer', $json);
		return C\View::SUCCESS;
	}

	protected function getViewClass () {
		return 'JSONView';
	}
}
