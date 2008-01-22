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

  <!-- Controls whether all PHP CodeSniffer errors and warnings should be listed.
       Set to 'false' for showing the warnings -->
  <xsl:param name="checkstyle.hide.warnings" select="'true'"/>

  <xsl:template match="/" mode="checkstyle">
    <xsl:variable name="total.error.count" select="count(cruisecontrol/checkstyle/file/error[@severity='error'])" />
    <xsl:variable name="total.warning.count" select="count(cruisecontrol/checkstyle/file/error[@severity='warning'])" />

    <table class="result" align="center">
      <thead>
        <tr>
          <th colspan="3">
            PHP CodeSniffer errors/warnings (<xsl:value-of select="$total.error.count"/>
            / <xsl:value-of select="$total.warning.count"/>)
          </th>
        </tr>
      </thead>
      <tbody>
        <xsl:for-each select="cruisecontrol/checkstyle">
          <xsl:apply-templates select="." mode="checkstyle">
            <xsl:with-param name="total.error.count" select="$total.error.count" />
            <xsl:with-param name="total.warning.count" select="$total.warning.count" />
          </xsl:apply-templates>
        </xsl:for-each>
      </tbody>
    </table>
  </xsl:template>

  <xsl:template match="checkstyle[file/error]" mode="checkstyle">
    <xsl:param name="total.error.count" />
    <xsl:param name="total.warning.count" />
    <xsl:choose>
      <xsl:when test="$checkstyle.hide.warnings = 'true' and $total.error.count = 0">
        <tr>
          <td class="checkstyle-data" colspan="3">
            <xsl:value-of select="$total.warning.count"/> warnings
          </td>
        </tr>
      </xsl:when>
      <xsl:when test="$checkstyle.hide.warnings = 'true'">
        <xsl:apply-templates select="file/error[@severity='error']" mode="checkstyle"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:apply-templates select="file/error" mode="checkstyle"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="error" mode="checkstyle">
    <tr>
      <xsl:if test="position() mod 2 = 1">
        <xsl:attribute name="class">oddrow</xsl:attribute>
      </xsl:if>
      <td class="{@severity}"><xsl:value-of select="../@name" /></td>
      <td><xsl:value-of select="@line" /></td>
      <td><xsl:value-of select="@message" /></td>
    </tr>
  </xsl:template>

  <xsl:template match="/">
    <xsl:apply-templates select="." mode="checkstyle"/>
  </xsl:template>

</xsl:stylesheet>
