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
		if ($domain->shift() == 'test') {
			$domain->shift();
		}
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
	public function getTrackingCode (UserAgent $useragent = null) {
		if (BS_DEBUG) {
			return null;
		}

		if (!$useragent) {
			$useragent = $this->request->getUserAgent();
		}

		if ($useragent->isMobile()) {
			$renderer = new ImageElement;
			$renderer->setURL($this->getBeaconURL());
		} else {
			$renderer = new Smarty;
			$renderer->setUserAgent($useragent);
			$renderer->setTemplate('GoogleAnalytics');
			$renderer->setAttribute('params', $this);
		}
		return $renderer->getContents();
	}

	private function getBeaconURL () {
		$url = URL::create();
		$url['path'] = BS_SERVICE_GOOGLE_ANALYTICS_BEACON_HREF;
		$url->setParameter('guid', 'ON');
		$url->setParameter('utmac', 'MO-' . $this->getID());
		$url->setParameter('utmn', Numeric::getRandom(0, 0x7fffffff));
		$url->setParameter('utmp', $this->request->getURL()->getFullPath());
		if (StringUtils::isBlank($referer = $this->controller->getAttribute('REFERER'))) {
			$referer = '-';
		}
		$url->setParameter('utmr', $referer);
		return $url;
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

