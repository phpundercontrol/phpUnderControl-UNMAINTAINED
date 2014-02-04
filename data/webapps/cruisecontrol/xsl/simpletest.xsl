<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" >
  <xsl:output method="html" encoding="UTF-8" indent="yes"/>

  <xsl:template match="/" mode="simpletest">
    <table class="result" align="center">
      <thead>
        <tr>
          <th colspan="4">
            SimpleTest Unit Tests: (<xsl:value-of select="sum( //case/pass | //case/fail )" />)
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td colspan="4">
            Total Pass case(s): (<xsl:value-of select="sum(//case/pass)" />)
          </td>
        </tr>
        <tr>
          <td colspan="4">
            Total Fail case(s): (<xsl:value-of select="sum(//case/fail)" />)
          </td>
        </tr>
        <tr>
          <td colspan="4">
            Total Exception(s): (<xsl:value-of select="sum(//case/exception)" />)
          </td>
        </tr>
      </tbody>
    </table>
  </xsl:template>

</xsl:stylesheet>

