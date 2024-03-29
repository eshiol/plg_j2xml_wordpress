/**
 * @package     Joomla.Plugins
 * @subpackage  J2xml.Wordpress
 *
 * @version     __DEPLOY_VERSION__
 * @since       4.4.29
 *
 * @author      Helios Ciancio <info (at) eshiol (dot) it>
 * @link        https://www.eshiol.it
 * @copyright   Copyright (C) 2014 - 2023 Helios Ciancio. All Rights Reserved
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL v3
 * J2XML is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License
 * or other free or open source software licenses.
 */

// Avoid `console` errors in browsers that lack a console.
(function () {
	var methods = [
		'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error',
		'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log',
		'profile', 'profileEnd', 'table', 'time', 'timeEnd', 'timeStamp',
		'trace', 'warn'
	];
	console = window.console = window.console || {};
	methods.forEach(function (method) {
		if (!console[method]) {
			console[method] = function () {};
		}
	});
}());
 
if (typeof(eshiol) === 'undefined') {
	eshiol = {};
}

if (typeof(eshiol.j2xml) === 'undefined') {
	eshiol.j2xml = {};
}

if (typeof(eshiol.j2xml.convert) === 'undefined') {
	eshiol.j2xml.convert = [];
}

eshiol.j2xml.wordpress = {};
eshiol.j2xml.wordpress.version = '__DEPLOY_VERSION__';
eshiol.j2xml.wordpress.requires = '18.8.309';

console.log('J2XML - Wordpress Importer v'+eshiol.j2xml.wordpress.version);

/**
 *
 * @param {} root
 * @return  {}
 */
eshiol.j2xml.convert.push(function(xml)
{  
	console.log('eshiol.j2xml.convert.wordpress');
	if (versionCompare(eshiol.j2xml.version, eshiol.j2xml.wordpress.requires) < 0)
	{
		eshiol.renderMessages({
			'error': ['J2XML - Wordpress Importer v'+eshiol.j2xml.wordpress.version+' requires J2XML v3.7.181']
		});
		return false;
	}
//	console.log(xml);

   	xmlDoc = jQuery.parseXML(xml);
	$xml = jQuery(xmlDoc);
	root = $xml.find(":root")[0];

	if ((root.nodeName == "rss") && (jQuery(root).attr('version') == '2.0'))
	{
		channel = jQuery(root).find("channel");
		if (channel.length == 0) return xml;
		
		generator = jQuery(channel[0]).find("generator");
		if (generator.length == 0) return xml;
		
		if (!generator.text().match(/(http|https):\/\/wordpress.(org|com)\//)) return xml;

		version = jQuery(channel[0]).find("wxr_version");
		if (version.length == 0)
		{
			var v = '12';
		}
		else if (jQuery(version[0]).text() == '1.1')
		{
			var v = '11';
		}
		else
		{
			var v = '12';
		}

		var xmlResp = new DOMParser();
		var xmlHttp = new XMLHttpRequest();
		xmlHttp.open("GET", '../plugins/j2xml/wordpress/'+v+'.xsl', false);
		// Make sure the returned document has the correct MIME type
		xmlHttp.overrideMimeType("application/xslt+xml");
		xmlHttp.send(null);
		this.Processor = new XSLTProcessor();
		// Just interpret the returned data as XML instead of parsing in a separate step
		this.Processor.importStylesheet(xmlHttp.responseXML);
		xml = this.Processor.transformToDocument(root)
		$xml = jQuery(xml);
		root = $xml.find(":root")[0];
		xml = eshiol.XMLToString(root);
	}
	return xml;
});
