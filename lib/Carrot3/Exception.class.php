<?php
namespace Carrot3;

class Exception extends \Exception {
	use BasicObject;

	public function __construct ($message = null, $code = 0, Exception $prev = null) {
		if ($message instanceof MessageContainer) {
			$message = $message->getMessage();
		}
		if (!is_numeric($code)) {
			$code = 0;
		}
		parent::__construct($message, $code, $prev);
		if ($this->isLoggable()) {
			LogManager::getInstance()->put($this);
		}
		if ($this->isAlertable()) {
			$this->alert();
		}
	}

	public function alert () {
		$json = new JSONRenderer;
		$json->setContents([
			'service' => $this->controller->getHost()->getName(),
			'class' => Utils::getClass($this),
			'message' => $this->getMessage(),
		]);
		$alerter = $this->loader->createObject(BS_ALERT_CLASS);
		if ($alerter instanceof ExceptionAlerter) {
			$alerter->alert($json);
		}
	}

	public function isLoggable ():bool {
		return true;
	}

	public function isAlertable ():bool {
		return false;
	}
}
