<%--********************************************************************************
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
********************************************************************************--%>
<%@ page errorPage="/error.jsp" contentType="text/html; charset=UTF-8"%>
<%@ taglib uri="/WEB-INF/cruisecontrol-jsp11.tld" prefix="cruisecontrol"%>
<%@ page import="net.sourceforge.cruisecontrol.*" %>
<%@ page import="java.io.IOException" %>
<%@ page import="java.net.InetAddress" %>
<%@ page import="java.net.URL" %>
<%@ page import="java.text.DateFormat" %>
<%@ page import="java.io.File" %>
<%@ page import="java.util.Arrays" %>
<%@ page import="java.text.ParseException" %>
<%@ page import="java.io.BufferedReader" %>
<%@ page import="java.io.FileReader" %>
<%@ page import="java.util.HashMap" %>
<%@ page import="java.util.Date" %>
<%@ page import="java.util.Comparator" %>
<%@ page import="java.util.Map" %>


<%
  final DateFormat dateTimeFormat = DateFormat.getDateTimeInstance(DateFormat.SHORT, DateFormat.SHORT, request.getLocale());
  final DateFormat dateOnlyFormat = DateFormat.getDateInstance(DateFormat.SHORT, request.getLocale());
  final DateFormat timeOnlyFormat = DateFormat.getTimeInstance(DateFormat.SHORT, request.getLocale());

  final Date now = new Date();
  final String dateNow = dateTimeFormat.format(now);
%>

<%
  class SortableStatus implements Comparable {
    private ProjectState state;
    private String importance;
    private int sortOrder;

    public SortableStatus(ProjectState state, String importance, int sortOrder) {
      this.state = state;
      this.importance = importance;
      this.sortOrder = sortOrder;
    }

    public String toString() {
      return state != null ? state.getName() : "?";
    }

    public int getSortOrder() {
      return sortOrder;
    }

    public int compareTo(Object other) {
      SortableStatus that = (SortableStatus) other;
      return this.sortOrder - that.sortOrder;
    }

    public String getImportance() {
      return importance;
    }
  }

  class StatusCollection {
    private Map statuses = new HashMap();
    private SortableStatus unknown = new SortableStatus(null, "dull", -1);

    public void add(ProjectState state, String importance) {
      statuses.put(state.getDescription(), new SortableStatus(state, importance, statuses.size()));
    }

    public SortableStatus get(String statusDescription) {
      Object status = statuses.get(statusDescription);
      if (status != null) {
        return (SortableStatus) status;
      }
      return unknown;
    }
  }

%>

<%
  final StatusCollection statuses = new StatusCollection();
  statuses.add(ProjectState.PUBLISHING, "important");
  statuses.add(ProjectState.MODIFICATIONSET, "important");
  statuses.add(ProjectState.BUILDING, "important");
  statuses.add(ProjectState.MERGING_LOGS, "important");
  statuses.add(ProjectState.QUEUED, "normal");
  statuses.add(ProjectState.WAITING, "dull");
  statuses.add(ProjectState.IDLE, "dull");
  statuses.add(ProjectState.PAUSED, "dull");
  statuses.add(ProjectState.STOPPED, "dull");
%>
<cruisecontrol:jmxbase id="jmxBase"/>
<%
  String name = System.getProperty("ccname", "");
  String hostname = InetAddress.getLocalHost().getHostName();
  boolean jmxEnabled = true;
  URL jmxURLPrefix = new URL(jmxBase, "invoke?operation=build&objectname=CruiseControl+Project%3Aname%3D");

  final String statusFileName = application.getInitParameter("currentBuildStatusFile");

  String baseURL = request.getScheme() + "://" + request.getServerName() + ":" + request.getServerPort()
                   + request.getContextPath() + "/";
  String thisURL = request.getRequestURI();

  String sort = request.getParameter("sort");
  if(sort == null){
    sort = "none";
  }
%>


<%
  class Info implements Comparable {
    public static final int ONE_DAY = 1000 * 60 * 60 * 24;

    private BuildInfo latest;
    private BuildInfo lastSuccessful;
    private SortableStatus status;
    private Date statusSince;
    private String project;
    private String statusDescription;

    public Info(File logsDir, String project) throws ParseException, IOException {
      this.project = project;

      File projectLogDir = new File(logsDir, project);
      LogFile latestLogFile = LogFile.getLatestLogFile(projectLogDir);
      LogFile latestSuccessfulLogFile = LogFile.getLatestSuccessfulLogFile(projectLogDir);


      if (latestLogFile != null) {
        latest = new BuildInfo(latestLogFile);
      }
      if (latestSuccessfulLogFile != null) {
        lastSuccessful = new BuildInfo(latestSuccessfulLogFile);
      }

      File statusFile = new File(projectLogDir, statusFileName);
      BufferedReader reader = null;
      try {
        reader = new BufferedReader(new FileReader(statusFile));
        statusDescription = reader.readLine().replaceAll(" since", "");

        status = statuses.get(statusDescription);
        statusSince = new Date(statusFile.lastModified());
      }
      catch (Exception e) {
        status = statuses.unknown;
        statusSince = now;
      }
      finally {
        if (reader != null) {
          reader.close();
        }
      }
    }

    public String getLastBuildTime() {
      return getTime(latest);
    }

    public String getLastSuccessfulBuildTime() {
      return getTime(lastSuccessful);
    }

    private String getTime(BuildInfo build) {
      return build != null ? format(build.getBuildDate()) : "";
    }

    public String format(Date date) {
      if (date == null) {
        return "";
      }

      if ((now.getTime() < date.getTime())) {
        return dateTimeFormat.format(date);
      }

      if ((now.getTime() - date.getTime()) < ONE_DAY) {
        return timeOnlyFormat.format(date);
      }

      return dateOnlyFormat.format(date);
    }

    public String getStatusSince() {
      return statusSince != null ? format(statusSince) : "?";
    }

    public boolean failed() {
      return latest == null || ! latest.isSuccessful();
    }

    public SortableStatus getStatus() {
      return status;
    }

    public int compareTo(Object other) {
      Info that = (Info) other;

      int order = this.status.compareTo(that.status);
      if (order != 0) {
        return order;
      }

      return (int) (this.statusSince.getTime() - that.statusSince.getTime());
    }

    public String getLabel() {
      return lastSuccessful != null ? lastSuccessful.getLabel() : " ";
    }
  }

%>

<%
  final Map sortOrders = new HashMap();

  sortOrders.put("project", new Comparator() {
    public int compare(Object a, Object b) {
      Info infoA = (Info) a;
      Info infoB = (Info) b;
      return infoA.project.compareTo(infoB.project);
    }
  });

  sortOrders.put("status", new Comparator() {
    public int compare(Object a, Object b) {
      Info infoA = (Info) a;
      Info infoB = (Info) b;
      return infoA.compareTo(infoB);
    }
  });

  sortOrders.put("label", new Comparator() {
    public int compare(Object a, Object b) {
      Info infoA = (Info) a;
      Info infoB = (Info) b;
      return infoA.getLabel().compareTo(infoB.getLabel());
    }
  });

  sortOrders.put("last failure", new Comparator() {
    public int compare(Object a, Object b) {
      Info infoA = (Info) a;
      Info infoB = (Info) b;

      if (infoA.latest == null) {
        return 1;
      }

      if (infoB.latest == null) {
        return -1;
      }

      if (infoA.failed() != infoB.failed()) {
        return infoA.failed() ? -1 : 1;
      }

      return infoB.latest.compareTo(infoA.latest);
    }
  });

  sortOrders.put("last successful", new Comparator() {
    public int compare(Object a, Object b) {
      Info infoA = (Info) a;
      Info infoB = (Info) b;

      if (infoA.lastSuccessful == null) {
        return 1;
      }
      if (infoB.lastSuccessful == null) {
        return -1;
      }

      return infoB.lastSuccessful.compareTo(infoA.lastSuccessful);
    }
  });
%>

<html>
<head>
  <title><%= name%> phpUnderControl - SVN at <%= hostname %></title>

  <base href="<%=baseURL%>"/>
  <link type="application/rss+xml" rel="alternate" href="rss" title="RSS"/>
  <link type="text/css" rel="stylesheet" href="css/php-under-control.css"/>
  <META HTTP-EQUIV="Refresh" CONTENT="10" URL="<%=thisURL%>?sort=<%=sort%>">
  <style type="text/css">
    thead td {
      padding: 2 5
    }

    .data {
      padding: 2 5
    }

    .date {
      text-align: right;
    }

    .status-important {
      font-weight: bold;
    }

    .status-normal {
    }

    .status-dull {
      font-style: italic;
    }

    .failure {
      background-color: #fff;
      color: red;
      font-weight: bold
    }

    .currently-failing {
      color: red;
      font-weight: bold
    }

    .currently-passing {
      color: gray;
    }

    a.sort {
      color: firebrick;
    }

    a.sorted {
      color: darkblue;
    }

    .dateNow {
      font-size: 15px;
      font-style: italic;
    }

    .odd-row {
      background-color: #CCCCCC;
    }

    .header-row {
      background-color: white;
      color: darkblue;
    }
</style>

  <script language="JavaScript">
    function callServer(url, projectName) {
      document.getElementById('serverData').innerHTML = '<iframe src="' + url + '" width="0" height="0" frameborder="0"></iframe>';
      alert('Scheduling build for ' + projectName);
    }

    function checkIframe(stylesheetURL) {
      if (top != self) {//We are being framed!

        //For Internet Explorer
        if (document.createStyleSheet) {
          document.createStyleSheet(stylesheetURL);

        }
        else { //Non-ie browsers

          var styles = "@import url('" + stylesheetURL + "');";

          var newSS = document.createElement('link');

          newSS.rel = 'stylesheet';

          newSS.href = 'data:text/css,' + escape(styles);

          document.getElementsByTagName("head")[0].appendChild(newSS);

        }
      }
    }
  </script>
</head>


<body onload="checkIframe('<%=baseURL + "css/php-under-control.css"%>')">
  <div id="container">
  <cruisecontrol:link id="baseUrl" />
  <h1>
    <a href="<%=baseUrl%>">
      phpUnderControl  
    </a>
  </h1>
  <h1 style="padding: 3px 0;" class="white" align="center">
    <%= name%> phpUnderControl at <%= hostname %><span class="dateNow">[<%= dateNow %>]</span>
  </h1>
  <div id="serverData" class="hidden"></div>
  <form>
    <table style="width:65%;margin: 20px auto" align="center">
      <tbody>

      <tr><td colspan="2">&nbsp;</td></tr>
      <tr>
        <td class="header-row"><img border="0" src="images/bluestripestop.gif"/></td>
        <td class="header-row" align="right"><img border="0" src="images/bluestripestopright.gif"/></td>
      </tr>


      <tr><td colspan="2">
        <table class="result">
          <%
            String logDirPath = application.getInitParameter("logDir");
            if (logDirPath == null) {
          %><tr><td>You need to provide a value for the context parameter <code>&quot;logDir&quot;</code></td></tr><%
        }
        else {
          java.io.File logDir = new java.io.File(logDirPath);
          if (logDir.isDirectory() == false) {
        %><tr><td>Context parameter logDir needs to be set to a directory. Currently set to &quot;<%=logDirPath%>
          &quot;</td></tr><%
        }
        else {
          String[] projectDirs = logDir.list(new java.io.FilenameFilter() {
            public boolean accept(File dir, String name) {
              return (new File(dir, name).isDirectory());
            }
          });

          if (projectDirs.length == 0) {
        %><tr><td>no project directories found under <%=logDirPath%></td></tr><%
        }
        else {
        %> 
        <thead class="index-header">
          <tr>
            <th><a class='<%= "project".equals(sort) ? "sort" : "sorted" %>' href="<%=thisURL%>?sort=project">Project</a></th>
            <th><a class="<%= "status".equals(sort) ? "sort" : "sorted" %>" href="<%=thisURL%>?sort=status">Status <em>(since)</em></a></th>
            <th><a class="<%= "last failure".equals(sort) ? "sort" : "sorted" %>" href="<%=thisURL%>?sort=last failure">Last failure</a></th>
            <th><a class="<%= "last successful".equals(sort) ? "sort" : "sorted" %>" href="<%=thisURL%>?sort=last successful">Last successful</a></th>
            <th>Label</th>
            <% if (jmxEnabled) { %>
            <th></th>
            <% } //end if jmxEnabled %>
          </tr>
        </thead>


          <tbody>
            <%
              Info[] info = new Info[projectDirs.length];
              for (int i = 0; i < info.length; i++) {
                info[i] = new Info(logDir, projectDirs[i]);
              }

              Comparator order = (Comparator) sortOrders.get(sort);
              if (order == null) {
                Arrays.sort(info);
              }
              else {
                Arrays.sort(info, order);
              }

              for (int i = 0; i < info.length; i++) {
            %>
            <tr class="<%= (i % 2 == 1) ? "even-row" : "odd-row" %> ">
              <td class="data"><a href="buildresults/<%=info[i].project%>"><%=info[i].project%></a></td>
              <td class="data date status-<%= info[i].getStatus().getImportance() %>"><%= info[i].getStatus()%> <em>(<%= info[i].getStatusSince() %>)</em></td>
              <td style="background-color: #fff;" class="data date<%= (info[i].failed() ? " failure" : "") %>"><%= (info[i].failed()) ? info[i].getLastBuildTime() : "" %></td>
              <td class="data date"><%= info[i].getLastSuccessfulBuildTime() %></td>
              <td class="data"><%= info[i].getLabel()%></td>

              <% if (jmxEnabled) { %>
              <td class="data"><input id="<%= "force_" + info[i].project %>" type="button"
                                      onclick="callServer('<%= jmxURLPrefix.toExternalForm() + info[i].project %>', '<%=info[i].project%>')"
                                      class="button" value="Build"/></td>
              <% } %>
            </tr>

          </tbody>
          <%
                  }
                }
              }
            }
          %></table>


      </td></tr>
      <tr>
        <td bgcolor="#FFFFFF"><img border="0" src="images/bluestripesbottom.gif"/></td>
        <td align="right" bgcolor="#FFFFFF"><img border="0" src="images/bluestripesbottomright.gif"/></td>
      </tr>
      <tr><td colspan="2">&nbsp;</td></tr>

    </tbody>
    <tfoot>
        <tr>
            <td colspan="2" align="right"><a href="rss"><img border="0" src="images/rss.png"/></a></td>
        </tr>
    </tfoot>
  </table>
</form>
</div>
    <%@ include file="footer.jsp" %>
  </body>
</html>

