<!--
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
-->
<xsl:stylesheet version="2.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xs="http://www.w3.org/2001/XMLSchema"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:wp="http://wordpress.org/export/1.2/"
	>
<xsl:output 
	cdata-section-elements="title alias introtext fulltext attribs metadata name language"
	encoding="UTF-8"
	indent="yes"
	/>

<xsl:key name="categories" match="/rss/channel/item[(wp:post_type = 'news') or (wp:post_type = 'page') or (wp:post_type = 'post')]/category[@domain='category']" use="@nicename" />

<xsl:template match="/rss">
<j2xml version="19.2.0">
	<base><xsl:value-of select="/rss/channel/link"/></base>
	<xsl:apply-templates select="/rss/channel/wp:author" mode="wp"/>
	<xsl:apply-templates select="/rss/channel/item[(wp:post_type = 'news') or (wp:post_type = 'page') or (wp:post_type = 'post')]/category[@domain='category']" mode="wp">
		<xsl:sort order="ascending" select="text()"/>
	</xsl:apply-templates>
	<xsl:apply-templates select="/rss/channel/item[(wp:post_type = 'news') or (wp:post_type = 'page') or (wp:post_type = 'post')]" mode="wp"/>
</j2xml>
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
	<id><xsl:value-of select="wp:post_id"/></id>
	<title><xsl:value-of select="title"/></title>
	<catid><xsl:choose>
		<xsl:when test="category[@domain='category']/@nicename != ''"><xsl:value-of select="category[@domain='category']/@nicename"/></xsl:when>
		<xsl:otherwise>uncategorised</xsl:otherwise>
	</xsl:choose></catid>
	<alias><xsl:choose>
		<xsl:when test="wp:post_name != ''"><xsl:value-of select="wp:post_name"/></xsl:when>
		<xsl:otherwise><xsl:value-of select="translate(substring-after(link, 'http://'), './?=', '----')"/></xsl:otherwise>
	</xsl:choose></alias>
	<introtext><xsl:value-of select="content:encoded"/></introtext>
	<fulltext></fulltext>
	<state><xsl:choose>
		<xsl:when test="wp:status = 'publish'">1</xsl:when>
		<xsl:otherwise>0</xsl:otherwise>
	</xsl:choose></state>
	<created><xsl:value-of select="wp:post_date"/></created>
	<created_by><xsl:value-of select="dc:creator"/></created_by>
	<created_by_alias></created_by_alias>
	<modified></modified>
	<modified_by></modified_by>
	<publish_up><xsl:value-of select="pubDate"/></publish_up>
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
	<canonical><xsl:value-of select="link"/></canonical>
</content>
</xsl:template>

<xsl:template match="wp:author" mode="wp">
<user>
	<id><xsl:value-of select="wp:author_id"/></id>
	<name><xsl:value-of select="wp:author_display_name"/></name>
	<username><xsl:value-of select="wp:author_login"/></username>
	<email><xsl:value-of select="wp:author_email"/></email>
	<password></password>
	<block>1</block>
	<sendEmail>0</sendEmail>
	<registerDate><![CDATA[0000-00-00 00:00:00]]></registerDate>
	<lastvisitDate><![CDATA[0000-00-00 00:00:00]]></lastvisitDate>
	<activation/>
	<params><![CDATA[{"admin_style":"","admin_language":"","language":"","editor":"","helpsite":"","timezone":""}]]></params>
	<lastResetTime><![CDATA[0000-00-00 00:00:00]]></lastResetTime>
	<resetCount>0</resetCount>
	<otpKey/>
	<otep/>
	<requireReset>0</requireReset>
	<group><![CDATA[["Public","Registered"]]]></group>
</user>
</xsl:template>

</xsl:stylesheet>
