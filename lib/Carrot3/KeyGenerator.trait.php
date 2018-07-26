<?php
namespace Carrot3;

trait KeyGenerator {
	protected function createKey ($values):string {
		if ($values instanceof Serializable) {
			$values = $values->digest();
		}
		$values = Tuple::create($values);
		$values[] = Controller::getInstance()->getHost()->getName();
		$values[] = Utils::getClass($this);
		return Crypt::digest(
			(new PHPSerializer)->encode($values->decode())
		);
	}
}
