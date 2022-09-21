<?php
/**
 * @package     Joomla.Plugins
 * @subpackage  J2xml.Wordpress
 *
 * @version     __DEPLOY_VERSION__
 * @since       3.1
 *
 * @author      Helios Ciancio <info (at) eshiol (dot) it>
 * @link        https://www.eshiol.it
 * @copyright   Copyright (C) 2014 - 2022 Helios Ciancio. All Rights Reserved
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL v3
 * J2XML is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License
 * or other free or open source software licenses.
 */
 
// no direct access
defined('_JEXEC') or die('Restricted access.');

class PlgJ2xmlWordpressInstallerScript
{
	public function install($parent)
	{
		// Enable plugin
		$db  = JFactory::getDbo();
		$db->setQuery($db->getQuery(true)
			->update($db->quoteName('#__extensions'))
			->set($db->quoteName('enabled')   . ' = 1')
			->where($db->quoteName('type')    . ' = ' . $db->quote('plugin'))
			->where($db->quoteName('folder')  . ' = ' . $db->quote('j2xml'))
			->where($db->quoteName('element') . ' = ' . $db->quote('wordpress'))
		)->execute();
	}
}