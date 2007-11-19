<?xml version="1.0"?>
<!--********************************************************************************
 * CruiseControl, a Continuous Integration Toolkit
 * Copyright (c) 2001, ThoughtWorks, Inc.
 * 200 E. Randolph, 25th Floor
 * Chicago, IL 60601 USA
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *     + Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *
 *     + Redistributions in binary form must reproduce the above
 *       copyright notice, this list of conditions and the following
 *       disclaimer in the documentation and/or other materials provided
 *       with the distribution.
 *
 *     + Neither the name of ThoughtWorks, Inc., CruiseControl, nor the
 *       names of its contributors may be used to endorse or promote
 *       products derived from this software without specific prior
 *       written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE REGENTS OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
  <xsl:output method="html"/>
  <xsl:param name="viewcvs.url"/>
  <xsl:param name="cvsmodule" select="concat(/cruisecontrol/info/property[@name='projectname']/@value, '/source/src/')"/>
  <xsl:key name="source" match="error" use="@source"/>

  <xsl:include href="phphelper.xsl" />

  <xsl:template match="/">
    <xsl:call-template name="checkstyle-summary" />
    <xsl:call-template name="checkstyle-check-summary"/>
    <table align="center" cellpadding="2" cellspacing="0" border="0" width="98%">
      <colgroup>
        <col width="5%"></col>
        <col width="5%"></col>
        <col width="90%"></col>
      </colgroup>
      <xsl:for-each select="cruisecontrol/checkstyle">
        <xsl:apply-templates select="."/>
      </xsl:for-each>
    </table>
  </xsl:template>

  <xsl:template match="checkstyle[file/error]">

    <xsl:for-each select="file[error]">
      <xsl:sort data-type="number" order="descending" select="count(error)"/>
      <xsl:apply-templates select="."/>
    </xsl:for-each>
  </xsl:template>

  <xsl:template name="checkstyle-summary">
    <table align="center" cellpadding="2" cellspacing="0" border="0" width="98%">
      <tr><td class="header-title">PHP CodeSniffer Summary</td></tr>
      <tr><td class="header-data">
        <span class="header-label">Files: </span>
        <xsl:value-of select="count(/cruisecontrol/checkstyle/file[error])"/>
      </td></tr>
      <tr><td class="header-data">
        <span class="header-label">Errors: </span>
        <xsl:value-of select="count(/cruisecontrol/checkstyle/file/error[@severity='error'])"/>
      </td></tr>
      <tr><td class="header-data">
        <span class="header-label">Warnings: </span>
        <xsl:value-of select="count(/cruisecontrol/checkstyle/file/error[@severity='warning'])"/>
      </td></tr>
    </table>
  </xsl:template>

  <xsl:template name="checkstyle-check-summary">
    <p/>
    <table align="center" cellpadding="2" cellspacing="0" border="0" width="98%">
      <colgroup>
        <col width="5%"></col>
        <col width="85%"></col>
        <col width="5%"></col>
        <col width="5%"></col>
      </colgroup>
      <tr class="checkstyle-fileheader">
        <td></td>
        <td>PHP CodeSniffer violation</td>
        <td>Files</td>
        <td>Error/Warnings</td>
      </tr>
      <xsl:for-each select="/cruisecontrol/checkstyle/file">
        <xsl:variable name="errors" select="/cruisecontrol/checkstyle/file[@name=current()/@name]/error"/>
        <xsl:variable name="errorCount" select="count($errors)"/>
        <xsl:variable name="fileCount" select="count($errors/..)"/>
        <tr>
          <xsl:if test="position() mod 2 = 0">
            <xsl:attribute name="class">checkstyle-oddrow</xsl:attribute>
          </xsl:if>
          <td></td>
          <td class="checkstyle-data"><xsl:value-of select="@name"/></td>
          <td class="checkstyle-data" align="right"><xsl:value-of select="$fileCount"/></td>
          <td class="checkstyle-data" align="right"><xsl:value-of select="$errorCount"/></td>
        </tr>
      </xsl:for-each>
    </table>
  </xsl:template>

  <xsl:template match="file">
    <xsl:variable name="filename" select="translate(@name,'\','/')"/>
    <xsl:variable name="javaclass">
      <xsl:call-template name="phpname">
        <xsl:with-param name="filename" select="$filename"/>
      </xsl:call-template>
    </xsl:variable>
    <tr><td colspan="3"><br/></td></tr>
    <tr>
      <td class="checkstyle-fileheader" colspan="3">
        <xsl:value-of select="$javaclass"/>
        (<xsl:value-of select="count(error[@severity='error'])"/>
        / <xsl:value-of select="count(error)"/>)
      </td>
    </tr>
    <xsl:for-each select="error">
      <tr>
        <xsl:if test="position() mod 2 = 0">
          <xsl:attribute name="class">checkstyle-oddrow</xsl:attribute>
        </xsl:if>
        <td />
        <td class="checkstyle-{@severity}" align="right">
          <xsl:call-template name="viewcvs">
            <xsl:with-param name="file" select="@name"/>
            <xsl:with-param name="line" select="@line"/>
          </xsl:call-template>
        </td>
        <td class="checkstyle-{@severity}"><xsl:value-of select="@message"/></td>
      </tr>
    </xsl:for-each>
  </xsl:template>

  <xsl:template name="viewcvs">
    <xsl:param name="file"/>
    <xsl:param name="line"/>
    <xsl:choose>
      <xsl:when test="not($viewcvs.url)">
        <xsl:value-of select="$line"/>
      </xsl:when>
      <xsl:otherwise>
        <a>
          <xsl:attribute name="href">
            <xsl:value-of select="concat($viewcvs.url, $cvsmodule)"/>
            <xsl:value-of select="substring-after($file, $cvsmodule)"/>
            <xsl:text>?annotate=HEAD#</xsl:text>
            <xsl:value-of select="$line"/>
          </xsl:attribute>
          <xsl:value-of select="$line"/>
        </a>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

</xsl:stylesheet>
