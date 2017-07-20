<?php
/**
 * @version		3.7.29 plugins/j2xml/wordpress/enable.php
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

class PlgJ2xmlWordpressInstallerScript
{
	public function install($parent)
	{
		// Enable plugin
		$db  = JFactory::getDbo();
		$db->setQuery($db->getQuery(true)
			->update($db->qn('#__extensions'))
			->set($db->qn('enabled')   . ' = 1')
			->where($db->qn('type')    . ' = ' . $db->q('plugin'))
			->where($db->qn('folder')  . ' = ' . $db->q('j2xml'))
			->where($db->qn('element') . ' = ' . $db->q('wordpress'))
		)->execute();
	}
}