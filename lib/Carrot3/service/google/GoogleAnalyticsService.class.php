<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage service.google
 */

namespace Carrot3;

/**
 * Google Analytics
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class GoogleAnalyticsService extends ParameterHolder implements Assignable {
	use Singleton, BasicObject;

	/**
	 * @access protected
	 */
	protected function __construct () {
		$this['id'] = BS_SERVICE_GOOGLE_ANALYTICS_ID;
		$this['domain'] = $this->getRootDomainName();
	}

	private function getRootDomainName () {
		$domain = StringUtils::explode('.', $this->controller->getHost()->getName());
		$domain->unshift(null);
		return $domain->join('.');
	}

	/**
	 * アカウントIDを返す
	 *
	 * @access public
	 * @return string アカウントID
	 */
	public function getID () {
		return $this['id'];
	}

	/**
	 * アカウントIDを設定
	 *
	 * @access public
	 * @param string $id アカウントID
	 */
	public function setID ($id) {
		$this['id'] = $id;
	}

	/**
	 * トラッキングコードを返す
	 *
	 * @access public
	 * @param UserAgent $useragent 対象UserAgent
	 * @return string トラッキングコード
	 */
	public function createTrackingCode (UserAgent $useragent = null) {
		if (!$useragent) {
			$useragent = $this->request->getUserAgent();
		}
		if (BS_DEBUG || $useragent->isMobile()) {
			return null;
		}
		$renderer = new Smarty;
		$renderer->setUserAgent($useragent);
		$renderer->setTemplate('GoogleAnalytics');
		$renderer->setAttribute('params', $this);
		return $renderer->getContents();
	}

	/**
	 * アサインすべき値を返す
	 *
	 * @access public
	 * @return mixed アサインすべき値
	 */
	public function assign () {
		return $this->params;
	}
}

