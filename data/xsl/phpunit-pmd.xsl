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

    <xsl:param name="pmd.warning.threshold" select="11"/>

    <xsl:template match="/" mode="pmd">
        <xsl:apply-templates select="cruisecontrol/pmd" mode="pmd"/>
    </xsl:template>

    <xsl:template match="pmd[file/violation]" mode="pmd">
        <xsl:variable name="total.error.count" select="count(file/violation[@priority &lt; $pmd.warning.threshold])" />
        <xsl:variable name="total.warning.count" select="count(file/violation) + count(//pmd-cpd/duplication)" />
        <table align="center" cellpadding="2" cellspacing="0" border="0" width="98%">
          <colgroup>
              <col width="45%"></col>
              <col width="5%"></col>
              <col width="50%"></col>
          </colgroup>
          <tr>
            <td class="checkstyle-sectionheader" colspan="3">
                PHPUnit PMD errors/warnings (<xsl:value-of select="$total.error.count"/>
                / <xsl:value-of select="$total.warning.count"/>)
            </td>
          </tr>
         <xsl:choose>
          <xsl:when test="$total.error.count = 0">
             <tr>
              <td class="checkstyle-data" colspan="3"><xsl:value-of select="$total.warning.count"/> warnings</td>
             </tr>
           </xsl:when>
           <xsl:otherwise>
            <xsl:for-each select="file/violation[@priority &lt; $pmd.warning.threshold]" >
              <tr>
                <xsl:if test="position() mod 2 = 1">
                  <xsl:attribute name="class">checkstyle-oddrow</xsl:attribute>
                </xsl:if>
                <td class="checkstyle-data"><xsl:value-of select="../@name" /></td>
                <td class="checkstyle-data" align="right"><xsl:value-of select="@line" /></td>
                <td class="checkstyle-data"><xsl:value-of select="." /></td>
              </tr>
            </xsl:for-each>
           </xsl:otherwise>
          </xsl:choose>
      </table>
    </xsl:template>

    <xsl:template match="/">
        <xsl:apply-templates select="." mode="pmd"/>
    </xsl:template>
</xsl:stylesheet>
