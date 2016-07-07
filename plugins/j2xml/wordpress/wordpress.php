<?php
/**
 * @version		4.4.23 plugins/j2xml/wordpress/wordpress.php
 * 
 * @package		J2XML
 * @subpackage	plg_j2xml_wordpress
 * @since		3.1
 *
 * @author		Helios Ciancio <info@eshiol.it>
 * @link		http://www.eshiol.it
 * @copyright	Copyright (C) 2010, 2016 Helios Ciancio. All Rights Reserved
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL v3
 * J2XML is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License 
 * or other free or open source software licenses.
 */
 
// no direct access
defined('_JEXEC') or die('Restricted access.');

use Joomla\Registry\Registry;

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
	 * @param object $config  The object that holds the plugin parameters
	 */
	function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);		

		// Get the parameters.
		// TODO: merge $this->params and $config['params']
		if (isset($config['params']))
		{
			if ($config['params'] instanceof Registry)
			{
				$this->_params = $config['params'];
			}
			else
			{
				$this->_params = (version_compare(JPlatform::RELEASE, '12', 'ge') ? new Registry : new JRegistry);
				$this->_params->loadString($config['params']);
			}
		}
		
		$lang = JFactory::getLanguage();
		$lang->load('plg_j2xml_wordpress', JPATH_SITE, null, false, false)
			|| $lang->load('plg_j2xml_wordpress', JPATH_ADMINISTRATOR, null, false, false)
			|| $lang->load('plg_j2xml_wordpress', JPATH_SITE, null, true)
			|| $lang->load('plg_j2xml_wordpress', JPATH_ADMINISTRATOR, null, true);	

		JLog::addLogger(array('text_file' => 'j2xml.php', 'extension' => 'plg_j2xml_wordpress'), JLog::ALL, array('plg_j2xml_wordpress'));
		JLog::addLogger(array('logger' => 'messagequeue', 'extension' => 'plg_j2xml_wordpress'), JLOG::ALL & ~JLOG::DEBUG, array('plg_j2xml_wordpress'));
	}

	/**
	 * Method is called by 
	 *
	 * @access	public
	 */
	public function onBeforeImport($context, &$xml)
	{
		JLog::add(new JLogEntry(__METHOD__,JLOG::DEBUG,'plg_j2xml_wordpress'));
		JLog::add(new JLogEntry($context,JLOG::DEBUG,'plg_j2xml_wordpress'));
		JLog::add(new JLogEntry(print_r($this->_params, true),JLOG::DEBUG,'plg_j2xml_wordpress'));
		
		if (get_class($xml) != 'SimpleXMLElement')
			return false;

		$error = false;
		if (!class_exists('XSLTProcessor'))
		{
			JLog::add(new JLogEntry(JText::_('PLG_J2XML_WORDPRESS').' '.JText::_('PLG_J2XML_WORDPRESS_MSG_REQUIREMENTS_XSL')),JLOG::WARNING,'plg_j2xml_wordpress');
			$error = true;
		}
		
		if (version_compare(J2XMLVersion::getShortVersion(), '16.7.0') == -1)
		{
			JLog::add(new JLogEntry(JText::_('PLG_J2XML_WORDPRESS').' '.JText::_('PLG_J2XML_WORDPRESS_MSG_REQUIREMENTS_LIB')),JLOG::WARNING,'plg_j2xml_wordpress');
			$error = true;
		}

		if ($error) return false;

		$namespaces = $xml->getNamespaces(true);
		if (!isset($namespaces['wp']))
			return true;
		if ($generator = $xml->xpath('/rss/channel/generator'))
			if (preg_match("/(http|https):\/\/wordpress.(org|com)\//", (string)$generator[0]) == false)
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

		$xsl = file_get_contents(JPATH_ROOT.'/plugins/j2xml/wordpress/'.$version.'.xsl');

		if ($this->_params->get('readmore', 1))
		{
			str_replace(		
				array(
					'<introtext><xsl:value-of select="content:encoded"/></introtext>',
					'<fulltext></fulltext>'
				),
				array(
					'<introtext><xsl:choose><xsl:when test="contains(content:encoded, \'&lt;!--more--&gt;\')"><xsl:value-of select="substring-before(content:encoded, \'&lt;!--more--&gt;\')"/></xsl:when><xsl:otherwise><xsl:value-of select="content:encoded"/></xsl:otherwise></xsl:choose></introtext>',
					'<fulltext><xsl:if test="contains(content:encoded, \'&lt;!--more--&gt;\')"><xsl:value-of select="substring-after(content:encoded, \'&lt;!--more--&gt;\')"/></xsl:if></fulltext>'
				),
				$xsl
			);
		}

		if ($post_type != 'post')
		{
			$post_query = "wp:post_type = 'post'";
			$unsupported_query = "wp:post_type != 'post'";
			foreach (explode(',', str_replace(' ', '', $this->_params->get('post_type'))) as $type)
			{
				$post_query .=  " or wp:post_type = '".$type."'";
				$unsupported_query .=  " and wp:post_type != '".$type."'";
			}
			$xsl = str_replace(
				"[wp:post_type = 'post']",
				"[".$post_query."]",
				$xsl
			);
		}
		$test_unsupported = "[".$unsupported_query."]";
		JLog::add(new JLogEntry($xsl,JLOG::DEBUG,'plg_j2xml_wordpress'));
		
		$xslt = new XSLTProcessor();
		$xslfile = new DOMDocument();
		$xslfile->loadXML($xsl);
		
		$post_types = $xml->xpath('/rss/channel/item'.$test_unsupported.'/wp:post_type');
		//foreach(array_unique($post_types) as $unsupported)
		foreach(array_unique($post_types) as $unsupported)
		{
			JLog::add(new JLogEntry(JText::sprintf('PLG_J2XML_WORDPRESS_MSG_POSTTYPE_NOT_SUPPORTED', $unsupported),JLOG::WARNING,'plg_j2xml_wordpress'));
		}

		$xslt->importStylesheet($xslfile);
		$xml = $xslt->transformToXML($xml);
		$xml = simplexml_load_string($xml);

		return true;
	}
}
