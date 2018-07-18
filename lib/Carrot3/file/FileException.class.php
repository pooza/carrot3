<?php
namespace Carrot3;

class FileException extends Exception {
	public function isAlertable ():bool {
		return true;
	}
}
