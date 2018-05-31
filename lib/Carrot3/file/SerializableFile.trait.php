<?php
namespace Carrot3;

trait SerializableFile {
	use SerializableObject;

	public function digest ():?string {
		return $this->getID();
	}

	public function getSerialized ():?Tuple {
		if ($this->isExists()) {
			$date = $this->getUpdateDate();
			if ($value = (new SerializeHandler)->getAttribute($this, $date)) {
				return Tuple::create($value);
			}
		}
		$this->removeSerialized();
		return null;
	}
}
