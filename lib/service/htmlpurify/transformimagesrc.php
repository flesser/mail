<?php

/**
 * @author Jakob Sack <jakob@owncloud.org>
 * @author Jakob Sack <mail@jakobsack.de>
 *
 * ownCloud - Mail
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Mail\Service\HtmlPurify;
use HTMLPurifier_AttrTransform;
use HTMLPurifier_Config;
use HTMLPurifier_Context;
use HTMLPurifier_URI;
use HTMLPurifier_URIFilter;
use HTMLPurifier_URIParser;
use OCP\IURLGenerator;
use OCP\Util;

/**
 * Adds copies src to data-src on all img tags.
 */
class TransformImageSrc extends HTMLPurifier_AttrTransform {
	/**
	* @type HTMLPurifier_URIParser
	*/
	private $parser;

	/** @var IURLGenerator */
	private $urlGenerator;

	public function __construct(IURLGenerator $urlGenerator) {
		$this->parser = new HTMLPurifier_URIParser();
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * @param array $attr
	 * @param HTMLPurifier_Config $config
	 * @param HTMLPurifier_Context $context
	 * @return array
	 */
	public function transform($attr, $config, $context) {
		if ($context->get('CurrentToken')->name !== 'img' ||
			!isset($attr['src'])) {
			return $attr;
		}

		// Block tracking pixels
		if (isset($attr['width']) && isset($attr['height']) &&
			(int)$attr['width'] < 5 && (int)$attr['height'] < 5){
			// Replace with a transparent png in case it's important for the layout
			$attr['src'] = Util::imagePath('mail', 'blocked-image.png');
			$attr = $this->setDisplayNone($attr);
			return $attr;
		}

		// Do not block images attached to the email
		$url = $this->parser->parse($attr['src']);
		if ($url->host === Util::getServerHostName() && $url->path === $this->urlGenerator->linkToRoute('mail.proxy.proxy')) {
			$attr['data-original-src'] = $attr['src'];
			$attr['src'] = Util::imagePath('mail', 'blocked-image.png');
			$attr = $this->setDisplayNone($attr);
		}
		return $attr;
	}

	/**
	 * @param array $attr
	 * @return array
	 *
	 * Sets html attribute style="display: none;", keeps old style
	 * attributes
	 */
	private function setDisplayNone($attr) {
		if (isset($attr['style'])) {
			$attr['style'] = 'display: none;'.$attr['style']; // the space is important for jquery!
		} else {
			$attr['style'] = 'display: none;';
		}
		return $attr;
	}
}
