<?php
/**
 * @version		3.3.21 plugins/j2xml/wordpress/enable.php
 * 
 * @package		J2XML
 * @subpackage	plg_j2xml_wordpress
 * @since		3.1
 *
 * @author		Helios Ciancio <info@eshiol.it>
 * @link		http://www.eshiol.it
 * @copyright	Copyright (C) 2010-2016 Helios Ciancio. All Rights Reserved
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL v3
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
		$query = $db->getQuery(true);
		$query->update('#__extensions');
		$query->set($db->quoteName('enabled') . ' = 1');
		$query->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));
		$query->where($db->quoteName('element') . ' = ' . $db->quote('wordpress'));
		$query->where($db->quoteName('folder') . ' = ' . $db->quote('j2xml'));
		$db->setQuery($query);
		$db->execute();
	}
}