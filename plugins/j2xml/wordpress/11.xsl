<!--
/**
 * @package     Joomla.Plugins
 * @subpackage  J2xml.Wordpress
 *
 * @version     __DEPLOY_VERSION__
 * @since       3.2
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

-->
<xsl:stylesheet version="2.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xs="http://www.w3.org/2001/XMLSchema"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:wp="http://wordpress.org/export/1.1/"
	>
<xsl:output 
	cdata-section-elements="title alias introtext fulltext attribs metadata name language username email"
	encoding="UTF-8"
	indent="yes"
	/>
 
<xsl:key name="categories" match="/rss/channel/item/category[@domain='category']" use="@nicename" />

<xsl:template match="/rss">
<j2xml version="12.5.0">
 	<xsl:apply-templates select="/rss/channel/wp:author" />
	<xsl:apply-templates select="/rss/channel/item/category[@domain='category']" mode="wp">
		<xsl:sort order="ascending" select="text()"/>
	</xsl:apply-templates>
	<xsl:apply-templates select="/rss/channel/item" mode="wp"/>
</j2xml>
</xsl:template>

<xsl:template match="wp:author">
<user>
	<id><xsl:value-of select="wp:author_id"/></id>
	<name><xsl:value-of select="wp:author_display_name"/></name>
	<username><xsl:value-of select="wp:author_login"/></username>
	<email><xsl:value-of select="wp:author_email"/></email>
	<password></password>
	<block>0</block>
	<sendEmail>0</sendEmail>
	<registerDate><![CDATA[0000-00-00 00:00:00]]></registerDate>
	<lastvisitDate><![CDATA[0000-00-00 00:00:00]]></lastvisitDate>
	<activation></activation>
	<params><![CDATA[{"emailVerified":"0","language":"","timezone":"Europe\/Rome","page_title":"Modifica il tuo account","show_page_title":"1","_empty_":""}]]></params>
	<lastResetTime><![CDATA[0000-00-00 00:00:00]]></lastResetTime>
	<resetCount>0</resetCount>
	<otpKey></otpKey>
	<otep></otep>
	<group><![CDATA[["Public","Registered"]]]></group>
</user>
</xsl:template>

<xsl:template match="category[generate-id(.) = generate-id(key('categories', @nicename))]" mode="wp">
<category>
	<id>0</id>
	<path><xsl:value-of select="@nicename"/></path>
	<extension>com_content</extension>
	<title><xsl:value-of select="."/></title>
	<alias><xsl:value-of select="@nicename"/></alias>
	<note></note>
	<description></description>
	<published>1</published>
	<access>1</access>
	<params>{}</params>
	<metadesc></metadesc>
	<metakey></metakey>
	<metadata><![CDATA[{"author":"","robots":""}]]></metadata>
	<created_user_id></created_user_id>
	<created_time></created_time>
	<modified_user_id></modified_user_id>
	<modified_time></modified_time>
	<hits>0</hits>
	<language><xsl:value-of select="/rss/channel/language"/></language>
</category>
</xsl:template>

<xsl:template match="text()" mode="wp"></xsl:template>

<xsl:template match="item" mode="wp">
<content>
	<id><xsl:value-of select="substring-after(guid, '=')"/></id>
	<title><xsl:value-of select="title"/></title>
	<catid><xsl:value-of select="category[@domain='category']/@nicename"/></catid>
	<alias><xsl:choose>
		<xsl:when test="wp:post_name != ''"><xsl:value-of select="wp:post_name"/></xsl:when>
		<xsl:otherwise><xsl:value-of select="translate(substring-after(link, 'http://'), './?=', '----')"/></xsl:otherwise>
	</xsl:choose></alias>
	<introtext><xsl:value-of select="content:encoded"/></introtext>
	<fulltext></fulltext>
	<state><xsl:choose>
		<xsl:when test="status = 'publish'">1</xsl:when>
		<xsl:otherwise>0</xsl:otherwise>
	</xsl:choose></state>
	<created><xsl:value-of select="wp:post_date"/></created>
	<created_by><xsl:value-of select="dc:creator"/></created_by>
	<created_by_alias></created_by_alias>
	<modified></modified>
	<modified_by></modified_by>
	<publish_up>0000-00-00 00:00:00</publish_up>
	<publish_down>0000-00-00 00:00:00</publish_down>
	<images><![CDATA[{"image_intro":"","float_intro":"","image_intro_alt":"","image_intro_caption":"","image_fulltext":"","float_fulltext":"","image_fulltext_alt":"","image_fulltext_caption":""}]]></images>
	<urls><![CDATA[{"urla":null,"urlatext":"","targeta":"","urlb":null,"urlbtext":"","targetb":"","urlc":null,"urlctext":"","targetc":""}]]></urls>
	<attribs>{}</attribs>
	<version>1</version>
	<ordering>0</ordering>
	<metakey></metakey>
	<metadesc></metadesc>
	<access>1</access>
	<hits>0</hits>
	<metadata><![CDATA[{"robots":"","author":"","rights":"","xreference":""}]]></metadata>
	<language><xsl:value-of select="/rss/channel/language"/></language>
	<xreference></xreference>
	<featured>0</featured>
	<rating_sum>0</rating_sum>
	<rating_count>0</rating_count>
</content>
</xsl:template>

</xsl:stylesheet>
