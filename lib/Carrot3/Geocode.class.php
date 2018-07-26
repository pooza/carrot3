<?php
namespace Carrot3;

class Geocode extends ParameterHolder {
	private $stations;

	public function __construct (iterable $params = []) {
		$this->setParameters($params);
	}

	public function setParameter (?string $name, $value) {
		if ($name == 'lon') {
			$name = 'lng';
		}
		parent::setParameter($name, $value);
	}

	public function format ($separator = ',') {
		return $this['lat'] . $separator . $this['lng'];
	}

	public function createElement (iterable $params) {
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

	public function getStations (int $flags = 0) {
		if (!$this->stations) {
			$this->stations = Tuple::create();
			try {
				$service = new HeartRailsExpressService;
				$this->stations->setParameters($service->getStations($this, $flags));
			} catch (\Throwable $e) {
				// ログのみ
			}
		}
		return $this->stations;
	}
}
