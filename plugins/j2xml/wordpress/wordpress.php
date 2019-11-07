<?php
/**
 * @version		3.7.29 plugins/j2xml/wordpress/wordpress.php
 * 
 * @package		J2XML
 * @subpackage	plg_j2xml_wordpress
 * @since		3.1
 *
 * @author		Helios Ciancio <info@eshiol.it>
 * @link		http://www.eshiol.it
 * @copyright	Copyright (C) 2014, 2017 Helios Ciancio. All Rights Reserved
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL v3
 * J2XML is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License 
 * or other free or open source software licenses.
 */
 
// no direct access
defined('_JEXEC') or die('Restricted access.');

use Joomla\Registry\Registry;
use eshiol\j2xml\Version;

jimport('joomla.plugin.plugin');
jimport('joomla.application.component.helper');
jimport('joomla.filesystem.file');

class plgJ2xmlWordpress extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 */
	protected $autoloadLanguage = true;

	/**
	 * Constructor
	 *
	 * @param  object  $subject  The object to observe
	 * @param  array   $config   An array that holds the plugin configuration
	 *
	 */
	function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		if ($this->params->get('debug') || defined('JDEBUG') && JDEBUG)
		{
			JLog::addLogger(array('text_file' => $this->params->get('log', 'eshiol.log.php'), 'extension' => 'plg_j2xml_wordpress_file'), JLog::ALL, array('plg_j2xml_wordpress'));
		}

		if (PHP_SAPI == 'cli')
		{
			JLog::addLogger(array('logger' => 'echo', 'extension' => 'plg_j2xml_wordpress'), JLOG::ALL & ~JLOG::DEBUG, array('plg_j2xml_wordpress'));
		}
		else
		{
			JLog::addLogger(array('logger' => $this->params->get('logger', 'messagequeue'), 'extension' => 'plg_j2xml_wordpress'), JLOG::ALL & ~JLOG::DEBUG, array('plg_j2xml_wordpress'));
			if ($this->params->get('phpconsole') && class_exists('JLogLoggerPhpconsole'))
			{
				JLog::addLogger(array('logger' => 'phpconsole', 'extension' => 'plg_j2xml_wordpress_phpconsole'),  JLOG::DEBUG, array('plg_j2xml_wordpress'));
			}
		}

		JLog::add(new JLogEntry(__METHOD__, JLOG::DEBUG, 'plg_j2xml_wordpress'));
	}

	/**
	 * Plugin that import wordpress data
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   mixed    &$xml     An object with a "text" property or the string to be imported.
	 *
	 * @return  boolean	True on success.
	 *
	 * @access	public
	 */
	public function onBeforeImport($context, &$xml)
	{
		JLog::add(new JLogEntry(__METHOD__, JLOG::DEBUG, 'plg_j2xml_wordpress'));
		JLog::add($context, JLOG::DEBUG, 'plg_j2xml_wordpress');
		JLog::add(print_r($this->params, true), JLOG::DEBUG, 'plg_j2xml_wordpress');

		if (get_class($xml) != 'SimpleXMLElement')
		{
			return false;
		}

		$error = false;
		if (!class_exists('XSLTProcessor'))
		{
			JLog::add(JText::_('PLG_J2XML_WORDPRESS').' '.JText::_('PLG_J2XML_WORDPRESS_MSG_REQUIREMENTS_XSL'), JLog::WARNING, 'plg_j2xml_wordpress');
			$error = true;
		}

		if (version_compare(Version::getFullVersion(), '17.7.301') == -1)
		{
			JLog::add(JText::_('PLG_J2XML_WORDPRESS').' '.JText::_('PLG_J2XML_WORDPRESS_MSG_REQUIREMENTS_LIB'), JLog::WARNING, 'plg_j2xml_wordpress');
			$error = true;
		}

		if ($error) return false;

		$namespaces = $xml->getNamespaces(true);
		if (!isset($namespaces['wp']))
		{
			return true;
		}
		if ($generator = $xml->xpath('/rss/channel/generator'))
		{
			if (preg_match("/(http|https):\/\/wordpress.(org|com)\//", (string)$generator[0]) == false)
			{
				return true;
			}
		}

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

		if ($this->params->get('readmore', 1))
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

		$default_post_query = $post_query = "(wp:post_type = 'post') or (wp:post_type = 'page')";
		$unsupported_query = "(wp:post_type != 'post') and (wp:post_type != 'page')";
		foreach (explode(',', str_replace(' ', '', $this->params->get('post_type'))) as $type)
		{
			$post_query .=  " or (wp:post_type = '".$type."')";
			$unsupported_query .=  " and (wp:post_type != '".$type."')";
		}
		$xsl = str_replace(
			"[".$default_post_query."]",
			"[".$post_query."]",
			$xsl
		);
		$test_unsupported = "[".$unsupported_query."]";
		JLog::add(new JLogEntry($xsl, JLOG::DEBUG, 'plg_j2xml_wordpress'));

		$xslt = new XSLTProcessor();
		$xslfile = new DOMDocument();
		$xslfile->loadXML($xsl);

		$post_types = $xml->xpath('/rss/channel/item'.$test_unsupported.'/wp:post_type');
		//foreach(array_unique($post_types) as $unsupported)
		foreach(array_unique($post_types) as $unsupported)
		{
			JLog::add(new JLogEntry(JText::sprintf('PLG_J2XML_WORDPRESS_MSG_POSTTYPE_NOT_SUPPORTED', $unsupported), JLog::WARNING, 'plg_j2xml_wordpress'));
		}

		$xslt->importStylesheet($xslfile);
		$xml = $xslt->transformToXML($xml);
		$xml = simplexml_load_string($xml);

		return true;
	}

	/**
	 * Method is called by index.php and administrator/index.php
	 *
	 * @access	public
	 */
	public function onAfterDispatch()
	{
		JLog::add(new JLogEntry(__METHOD__, JLOG::DEBUG, 'plg_j2xml_wordpress'));
	
		$app = JFactory::getApplication();
		if ($app->getName() != 'administrator')
		{
			return true;
		}
	
		$enabled = JComponentHelper::getComponent('com_j2xml', true);
		if (!$enabled->enabled)
		{
			return true;
		}
	
		$option = JRequest::getVar('option');
		$view = JRequest::getVar('view');
	
		if (($option == 'com_j2xml') && (!$view || $view == 'cpanel'))
		{
			$doc = JFactory::getDocument();
			if ($this->params->get('debug') || defined('JDEBUG') && JDEBUG)
			{
				JLog::add(new JLogEntry('loading j2xml.js...', JLOG::DEBUG, 'plg_j2xml_wordpress'));
				$doc->addScript("../media/plg_j2xml_wordpress/js/j2xml.js");
			}
			else
			{
				JLog::add(new JLogEntry('loading j2xml.min.js...', JLOG::DEBUG, 'plg_j2xml_wordpress'));
				$doc->addScript("../media/plg_j2xml_wordpress/js/j2xml.min.js");
			}
		}
		return true;
	}
}
