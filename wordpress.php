<?php
/**
 * @version		3.2.16 plugins/j2xml/wordpress/wordpress.php
 * 
 * @package		J2XML
 * @subpackage	plg_j2xml_wordpress
 * @since		3.1
 *
 * @author		Helios Ciancio <info@eshiol.it>
 * @link		http://www.eshiol.it
 * @copyright	Copyright (C) 2010-2015 Helios Ciancio. All Rights Reserved
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL v3
 * J2XML is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License 
 * or other free or open source software licenses.
 */
 
// no direct access
defined('_JEXEC') or die('Restricted access.');

jimport('joomla.plugin.plugin');
jimport('joomla.application.component.helper');
jimport('joomla.filesystem.file');
jimport('eshiol.j2xml.version');

class plgJ2XMLWordpress extends JPlugin
{
	var $_params = null;
	/**
	 * CONSTRUCTOR
	 * @param object $subject The object to observe
	 * @param object $params  The object that holds the plugin parameters
	 */
	function __construct(&$subject, $params)
	{
		parent::__construct($subject, $params);		

		$lang = JFactory::getLanguage();
		$lang->load('plg_j2xml_wordpress', JPATH_SITE, null, false, false)
			|| $lang->load('plg_j2xml_wordpress', JPATH_ADMINISTRATOR, null, false, false)
			|| $lang->load('plg_j2xml_wordpress', JPATH_SITE, null, true)
			|| $lang->load('plg_j2xml_wordpress', JPATH_ADMINISTRATOR, null, true);	
	}

	/**
	 * Method is called by 
	 *
	 * @access	public
	 */
	public function onBeforeImport($context, &$xml)
	{
		if (get_class($xml) != 'SimpleXMLElement')
			return false;
		
		$error = false;
		if (!class_exists('XSLTProcessor'))
		{
			JError::raiseWarning(1, JText::_('PLG_J2XML_WORDPRESS').' '.JText::_('PLG_J2XML_WORDPRESS_MSG_REQUIREMENTS_XSL'));
			$error = true;
		}
		
		if (version_compare(J2XMLVersion::getShortVersion(), '13.8.3') == -1)
		{
			JError::raiseWarning(1, JText::_('PLG_J2XML_WORDPRESS').' '.JText::_('PLG_J2XML_WORDPRESS_MSG_REQUIREMENTS_LIB'));
			$error = true;
		}

		if ($error) return false;
		
		$namespaces = $xml->getNamespaces(true);
		if (!isset($namespaces['wp']))
			return true;
		if ($generator = $xml->xpath('/rss/channel/generator'))
			if (preg_match("/http:\/\/wordpress.(org|com)\//", (string)$generator[0]) == false)
				return true;	

		$xml->registerXPathNamespace('wp', $namespaces['wp']);
		if (!($wp_version = $xml->xpath('/rss/channel/wp:wxr_version')))
		{
			return true;
		}
		else if ($wp_version[0] == '1.2') 
		{
			$version = 12;
		}
		else if ($wp_version[0] == '1.1')
		{		
			$version = 11;
		}
		else 
		{
			return true;
		}
		
		$xslt = new XSLTProcessor();
		$xslfile = new DOMDocument();
		if ($this->params->get('readmore', 1))
		{
			$xslfile->loadXML(
				str_replace(		
					array(
						'<introtext><xsl:value-of select="content:encoded"/></introtext>',
						'<fulltext></fulltext>'
					),
					array(
						'<introtext><xsl:choose><xsl:when test="contains(content:encoded, \'&lt;!--more--&gt;\')"><xsl:value-of select="substring-before(content:encoded, \'&lt;!--more--&gt;\')"/></xsl:when><xsl:otherwise><xsl:value-of select="content:encoded"/></xsl:otherwise></xsl:choose></introtext>',
						'<fulltext><xsl:if test="contains(content:encoded, \'&lt;!--more--&gt;\')"><xsl:value-of select="substring-after(content:encoded, \'&lt;!--more--&gt;\')"/></xsl:if></fulltext>'
					),
					file_get_contents(JPATH_ROOT.'/plugins/j2xml/wordpress/'.$version.'.xsl')
				)
			);
		}
		else
			$xslfile->load(JPATH_ROOT.'/plugins/j2xml/wordpress/'.$version.'.xsl');
		$xslt->importStylesheet($xslfile);
		$xml = $xslt->transformToXML($xml);
		$xml = simplexml_load_string($xml);
		return true;
	}
}
