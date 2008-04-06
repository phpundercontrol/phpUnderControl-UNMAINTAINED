<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="2.0">
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
  <xsl:output method="html" encoding="UTF-8" indent="yes"/>
  <xsl:decimal-format decimal-separator="." grouping-separator="," />

  <!-- 
    Root template
  -->
  <xsl:template match="/">
    <script type="text/javascript" language="JavaScript">
    // <!--
    // Function show/hide given div
    // -->
    function toggleDivVisibility(_div) {
        if (_div.style.display=="none") {
            _div.style.display="block";
        } else {
            _div.style.display="none";
        }
    }
    </script>
           
    <!-- Main table -->
    <table class="result">
      <colgroup>
        <col width="10%"/>
        <col width="45%"/>
        <col width="25%"/>
        <col width="10%"/>
        <col width="10%"/>
      </colgroup>
      <thead>
        <tr>
          <th colspan="3">Name</th>
          <th>Status</th>
          <th nowrap="nowrap">Time(s)</th>
        </tr>
      </thead>
      <tbody>
        <!-- display test suites -->
        <xsl:apply-templates select="//testsuites/testsuite">
          <xsl:sort select="count(testcase/error)" 
                    data-type="number" 
                    order="descending"/>
          <xsl:sort select="count(testcase/failure)" 
                    data-type="number" 
                    order="descending"/>
          <xsl:sort select="@package"/>
          <xsl:sort select="@name"/>
        </xsl:apply-templates>
      </tbody>
    </table>
  </xsl:template>
  
  <!--
    Test Suite Template
    Construct TestSuite section
  -->
  <xsl:template match="testsuite">
    <tr>
      <xsl:attribute name="class">
        <xsl:choose>
          <xsl:when test="testcase/error">error</xsl:when>
          <xsl:when test="testcase/failure">failure</xsl:when>
        </xsl:choose>
      </xsl:attribute>
      <th colspan="4">
        <xsl:choose>
          <xsl:when test="@fullPackage">
            <xsl:value-of select="concat(@fullPackage, '::', @name)"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="@name"/>
          </xsl:otherwise>
        </xsl:choose>
      </th>
      <th>
        <xsl:value-of select="format-number(@time,'0.000')"/>
      </th>
    </tr>
    <xsl:variable name="data.provider.prefix" select="concat(@name, '::')" />
    <!-- Display tests -->
    <xsl:apply-templates select="testcase"/>
    <!-- Display @dataProvider testsuites -->
    <xsl:apply-templates select="./testsuite[starts-with(@name, $data.provider.prefix)]" mode="data.provider">
      <xsl:with-param name="odd.or.even" select="count(testcase) mod 2" />
    </xsl:apply-templates>
    
    <tr><td colspan="5"><br /></td></tr>
    
    <!-- Include all sub test suites -->
    <xsl:apply-templates select="./testsuite[starts-with(@name, $data.provider.prefix) = false()]">
      <xsl:sort select="count(testcase/error)" 
                data-type="number" 
                order="descending"/>
      <xsl:sort select="count(testcase/failure)" 
                data-type="number" 
                order="descending"/>
      <xsl:sort select="@package"/>
      <xsl:sort select="@name"/>
    </xsl:apply-templates>
  </xsl:template>
  
  <!--
    Testcase template
    Construct testcase section
  -->
  <xsl:template match="testcase">
    <xsl:param name="odd.or.even" select="0" />
    <xsl:param name="sub.testcase" select="false()" />
    <tr>
      <xsl:attribute name="class">
        <xsl:choose>
          <xsl:when test="error">
            <xsl:text>error</xsl:text>
            <xsl:if test="position() mod 2 != $odd.or.even">
              <xsl:text> oddrow</xsl:text>
            </xsl:if>
          </xsl:when>
          <xsl:when test="failure">
            <xsl:text>failure</xsl:text>
            <xsl:if test="position() mod 2 != $odd.or.even">
              <xsl:text> oddrow</xsl:text>
            </xsl:if>
          </xsl:when>
          <xsl:otherwise>
            <xsl:text>success</xsl:text>
            <xsl:if test="position() mod 2 != $odd.or.even">
              <xsl:text> oddrow</xsl:text>
            </xsl:if>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:attribute>
      <td colspan="3">
        <xsl:attribute name="class">
          <xsl:if test="$sub.testcase">
            <xsl:text>sub </xsl:text>
          </xsl:if>
          <xsl:choose>
            <xsl:when test="error">
              <xsl:choose>
                <xsl:when test="error/@type = 'PHPUnit_Framework_SkippedTestError'">
                  <xsl:text>skipped</xsl:text>
                </xsl:when>
                <xsl:when test="error/@type = 'PHPUnit_Framework_IncompleteTestError'">
                  <xsl:text>unknown</xsl:text>
                </xsl:when>
                <xsl:otherwise>
                  <xsl:text>error</xsl:text>
                </xsl:otherwise>
              </xsl:choose>
            </xsl:when>
            <xsl:when test="failure">
              <xsl:text>failure</xsl:text>
            </xsl:when>
            <xsl:otherwise>
              <xsl:text>success</xsl:text>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:attribute>
        <xsl:if test="$sub.testcase">
          <xsl:text>#</xsl:text>
          <xsl:value-of select="position()" />
          <xsl:text> - </xsl:text>
        </xsl:if>
        <xsl:value-of select="@name"/>
      </td>
      <td>
        <xsl:choose>
          <xsl:when test="error">
            <a href="javascript:void(0)"
               onClick="toggleDivVisibility(document.getElementById('{concat('error.',../@package,'.',../@name,'.',@name)}'))">Error &#187;</a>
          </xsl:when>
          <xsl:when test="failure">
            <a href="javascript:void(0)"
               onClick="toggleDivVisibility(document.getElementById('{concat('failure.',../@package,'.',../@name,'.',@name)}'))">Failure &#187;</a>
          </xsl:when>
          <xsl:otherwise>Success</xsl:otherwise>
        </xsl:choose>
      </td>
      <xsl:choose>
        <xsl:when test="not(failure|error)">
          <td>
            <xsl:value-of select="format-number(@time,'0.000')"/>
          </td>
        </xsl:when>
        <xsl:otherwise>
          <td/>
        </xsl:otherwise>
      </xsl:choose>
    </tr>
    <xsl:if test="error">
      <tr>
        <td></td>
        <td colspan="4">
          <span id="{concat('error.',../@package,'.',../@name,'.',@name)}" class="testresults-output-div" style="display: none;">
            <h3>Error:</h3>
            <xsl:apply-templates select="error/text()" mode="newline-to-br"/>
          </span>
        </td>
      </tr>
    </xsl:if>
    <xsl:if test="failure">
      <tr>
        <td></td>
        <td colspan="4">
          <span id="{concat('failure.',../@package,'.',../@name,'.',@name)}" class="testresults-output" style="display: none;">
            <h3>Failure:</h3>
            <xsl:apply-templates select="failure/text()" mode="newline-to-br"/>
          </span>
        </td>
      </tr>
    </xsl:if>
  </xsl:template>
  
  <!--
    TestSuite/TestCase template
    for @dataProvider tests.
  -->
  <xsl:template match="testsuite" mode="data.provider">
    <xsl:param name="odd.or.even" select="0" />
    <tr>
      <xsl:if test="position() mod 2 != $odd.or.even">
        <xsl:attribute name="class">
          <xsl:text>oddrow</xsl:text>
        </xsl:attribute>
      </xsl:if>
      <td colspan="3">
        <xsl:attribute name="class">
          <xsl:choose>
            <xsl:when test="testcase/error">
              <xsl:text>error</xsl:text>
            </xsl:when>
            <xsl:when test="testcase/failure">
              <xsl:text>failure</xsl:text>
            </xsl:when>
            <xsl:otherwise>
              <xsl:text>success</xsl:text>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:attribute>
        <xsl:value-of select="substring-after(@name, '::')" />
      </td>
      <td>
        <xsl:if test="not(testcase/failure|testcase/error)">
          <xsl:text>Success</xsl:text>
        </xsl:if>
      </td>
      <td>
        <xsl:value-of select="format-number(@time,'0.000')"/>
      </td>
    </tr>
    <xsl:apply-templates select="testcase">
      <xsl:with-param name="odd.or.even" select="($odd.or.even + 1) mod 2" />
      <xsl:with-param name="sub.testcase" select="true()" />
    </xsl:apply-templates>
  </xsl:template>

</xsl:stylesheet>