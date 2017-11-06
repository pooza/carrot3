<?php
/**
 * @package __PACKAGE__
 * @subpackage Default
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

namespace Carrot3\DefaultModule;
use \Carrot3 as C;

class LoginAction extends C\Action {
	public function execute () {
		return C\Module::getInstance('AdminLog')->redirect();
	}

	public function getDefaultView () {
		$this->user->logout();
		return C\View::INPUT;
	}

	public function handleError () {
		return $this->getDefaultView();
	}

	public function validate () {
		$role = C\AdministratorRole::getInstance();
		$email = C\MailAddress::create($this->request['email']);
		if ($email->getContents() != $role->getMailAddress()->getContents()) {
			$this->request->setError('email', 'ユーザー又はパスワードが違います。');
		} else if (!$this->user->login($role, $this->request['password'])) {
			$this->request->setError('password', 'ユーザー又はパスワードが違います。');
		}
		return !$this->request->hasErrors();
	}
}
