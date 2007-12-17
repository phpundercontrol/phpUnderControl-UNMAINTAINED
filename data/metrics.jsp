<%@page import="net.sourceforge.cruisecontrol.*, net.sourceforge.cruisecontrol.chart.*"%>
<%@ taglib uri="/WEB-INF/lib/cewolf.jar" prefix="cewolf" %>
<%@ taglib uri="/WEB-INF/cruisecontrol-jsp11.tld" prefix="cruisecontrol"%>

<h2>Project Metric Summary</h2>
<cruisecontrol:buildInfo />
<dl>
  <dt>Number of Build Attempts</dt>  
  <dd><%=build_info.size() %></dd>
  <dt>Number of Broken Builds</dt>
  <dd><%=build_info.getNumBrokenBuilds() %></dd>
  <dt>Number of Successful Builds</dt>
  <dd><%=build_info.getNumSuccessfulBuilds() %></dd>
</dl>

<hr />

<%--
Displays the number of broken and successful builds 
--%>
<jsp:useBean id="pieData" class="net.sourceforge.cruisecontrol.chart.PieChartData" />
<cewolf:chart id="pie" title="Breakdown of build types" type="pie" >
    <cewolf:data>
        <cewolf:producer id="pieData">
          <cewolf:param name="buildInfo" value="<%=build_info%>" />
        </cewolf:producer>
    </cewolf:data>
</cewolf:chart>
<cewolf:img chartid="pie" renderer="cewolf" width="450" height="280" style="display:inline;margin:0 15px 15px 15px;"/>


<jsp:useBean id="chartData" class="net.sourceforge.cruisecontrol.chart.TimeChartData" />
<cewolf:chart id="chart" title="Breakdown of build types" type="timeseries"  xaxislabel="date" yaxislabel="time">
    <cewolf:data>
        <cewolf:producer id="chartData">
          <cewolf:param name="buildInfo" value="<%=build_info%>" />
        </cewolf:producer>
    </cewolf:data>
    <cewolf:chartpostprocessor id="chartData" />
</cewolf:chart>
<cewolf:img chartid="chart" renderer="cewolf" width="450" height="280" style="display:inline;margin:0 15px 15px 15px;"/>


<jsp:useBean id="coverageData" class="net.sourceforge.cruisecontrol.chart.XPathChartData" />
<%
  coverageData.add("Lines of code", "sum(/cruisecontrol/coverage/project/file/metrics/@loc)");
  coverageData.add("Non comment lines", "sum(/cruisecontrol/coverage/project/file/metrics/@ncloc)");
  coverageData.add("Executable lines", "count(/cruisecontrol/coverage/project/file/line)");
  coverageData.add("Covered lines", "count(/cruisecontrol/coverage/project/file/line[@count != 0])");
%>
<cewolf:chart id="coverageChart" title="Unit coverage" type="timeseries"  xaxislabel="date" yaxislabel="lines">
  <cewolf:data>
    <cewolf:producer id="coverageData">
      <cewolf:param name="buildInfo" value="<%=build_info%>" />
    </cewolf:producer>
  </cewolf:data>
  <cewolf:chartpostprocessor id="coverageData" />
</cewolf:chart>
<cewolf:img chartid="coverageChart" renderer="cewolf" width="450" height="280" style="display:inline;margin:0 15px 15px 15px;"/>

<jsp:useBean id="unitTestData" class="net.sourceforge.cruisecontrol.chart.XPathChartData" />
<%
  unitTestData.add("Total", "count(/cruisecontrol/testsuites//testcase)");
  unitTestData.add("Failures", "count(/cruisecontrol/testsuites//testcase[failure])");
%>
<cewolf:chart id="unitTestChart" title="Unit Tests" type="timeseries"  xaxislabel="date" yaxislabel="methods">
  <cewolf:data>
    <cewolf:producer id="unitTestData">
      <cewolf:param name="buildInfo" value="<%=build_info%>" />
    </cewolf:producer>
  </cewolf:data>
  <cewolf:chartpostprocessor id="unitTestData" />
</cewolf:chart>
<cewolf:img chartid="unitTestChart" renderer="cewolf" width="450" height="280" style="display:inline;margin:0 15px 15px 15px;"/>


<jsp:useBean id="numData" class="net.sourceforge.cruisecontrol.chart.XPathChartData" />
<%
  numData.add("Classes", "count(/cruisecontrol/coverage/project/file/class)");
  numData.add("Test Classes", "count(/cruisecontrol/testsuites//testsuite[testcase])");
  numData.add("Methods", "count(/cruisecontrol/coverage/project/file/line[@type='method'])");
  numData.add("Test Methods", "count(/cruisecontrol/testsuites//testsuite/testcase)");
%>
<cewolf:chart id="numChart" title="Test - Code ratio" type="timeseries"  xaxislabel="date" yaxislabel="classes / methods">
    <cewolf:data>
        <cewolf:producer id="numData">
          <cewolf:param name="buildInfo" value="<%=build_info%>" />
        </cewolf:producer>
    </cewolf:data>
    <cewolf:chartpostprocessor id="numData" />
</cewolf:chart>
<cewolf:img chartid="numChart" renderer="cewolf" width="450" height="280" style="display:inline;margin:0 15px 15px 15px;"/>

<jsp:useBean id="xpathData" class="net.sourceforge.cruisecontrol.chart.XPathChartData" />
<%
    xpathData.add("PHP CodeSniffer", "count(/cruisecontrol/checkstyle/file/error)");
    xpathData.add("PHPUnit PMD", "count(/cruisecontrol/pmd/file/violation)");
    xpathData.add("PHPdoc", "count(/cruisecontrol/build//target[@name='php-documentor']/task[@name='exec']/message[contains(text(), 'WARNING in') or contains(text(), 'WARNING:') or contains(text(), 'ERROR in') or contains(text(), 'ERROR:')])");
%>
<cewolf:chart id="chart" title="Coding violations" type="timeseries"  xaxislabel="date" yaxislabel="violations">
    <cewolf:data>
        <cewolf:producer id="xpathData">
          <cewolf:param name="buildInfo" value="<%=build_info%>" />
        </cewolf:producer>
    </cewolf:data>
    <cewolf:chartpostprocessor id="xpathData" />
</cewolf:chart>
<cewolf:img chartid="chart" renderer="cewolf" width="450" height="280" style="display:inline;margin:0 15px 15px 15px;"/>
