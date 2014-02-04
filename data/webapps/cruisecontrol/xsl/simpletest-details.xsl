<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" >
  <xsl:output method="html" encoding="UTF-8" indent="yes"/>

  <xsl:template match="/">
    <tr>
      <td>
        <style>
          #simpletest-result-set
          {
            background : white !important;
            margin : 2em;
            height : 0 !important;
          }

          #simpletest-result-set h1
          {
            margin : 0.5em;
            padding : 0.2em 0.5em;
            font-size : 2em !important;
          }

          .test-case
          {
            background : white !important;
            height : 10em !important;
          }

          .test-case h3
          {
            padding-left : 2em;
          }

          .module
          {
            background : white !important;
            border : solid thin black;
          }

          .pass, .fail, .exception
          {
            background : white !important;
            padding-left : 2em;
          }
        </style>

        <xsl:apply-templates select="//run" />
      </td>
    </tr>
  </xsl:template>

  <xsl:template match="run">
    <div id="simpletest-result-set">
      <h1>SimpleTest Result Set</h1>

      <ul><xsl:apply-templates select="//group/name[not(.=following::group/name)]" /></ul>
    </div>
  </xsl:template>

  <xsl:template match="group/name">
    <li class="module">
      <h2><xsl:value-of select="." /></h2>

      <xsl:variable name="group_name" select="." />
      <xsl:apply-templates select="//group[name=$group_name]/case" />
    </li>
  </xsl:template>

  <!-- xsl:template match="group">
    <li class="module">
      <h2><xsl:value-of select="name" /></h2>
      <xsl:apply-templates select="case" />
    </li>
  </xsl:template -->

  <xsl:template match="case">
    <div class="test-case">
      <h3><xsl:value-of select="name" /></h3>

      <div class="pass">
        <xsl:text>Pass case: </xsl:text>
        <xsl:value-of select="count(test/pass)" />
      </div>

      <div class="fail">
        <xsl:text>Fail case: </xsl:text>
        <xsl:value-of select="count(test/fail)" />
      </div>

      <div class="exception">
        <xsl:text>Exception(s): </xsl:text>
        <xsl:value-of select="count(test/exception)" />
        <xsl:value-of select="exception" />
      </div>
    </div>
  </xsl:template>

</xsl:stylesheet>

