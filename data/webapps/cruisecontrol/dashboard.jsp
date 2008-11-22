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
<%@ page import="java.net.InetAddress" %>
<%@ page import="java.net.URL" %>

<cruisecontrol:jmxbase id="jmxBase" />

<%

URL jmxURLPrefix = new URL(jmxBase, "invoke?operation=build&objectname=CruiseControl+Project%3Aname%3D");

final DateFormat dateTimeFormat = DateFormat.getDateTimeInstance(DateFormat.SHORT, DateFormat.SHORT, request.getLocale());
final DateFormat dateOnlyFormat = DateFormat.getDateInstance(DateFormat.SHORT, request.getLocale());
final DateFormat timeOnlyFormat = DateFormat.getTimeInstance(DateFormat.SHORT, request.getLocale());
  
final Date now = new Date();
final String statusFileName = application.getInitParameter("currentBuildStatusFile");

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

  final StatusCollection statuses = new StatusCollection();
  statuses.add(ProjectState.PUBLISHING, "important");
  statuses.add(ProjectState.MODIFICATIONSET, "important");
  statuses.add(ProjectState.BUILDING, "important");
  statuses.add(ProjectState.MERGING_LOGS, "important");
  statuses.add(ProjectState.BOOTSTRAPPING, "normal");
  statuses.add(ProjectState.QUEUED, "normal");
  statuses.add(ProjectState.WAITING, "dull");
  statuses.add(ProjectState.IDLE, "dull");
  statuses.add(ProjectState.PAUSED, "dull");
  statuses.add(ProjectState.STOPPED, "dull");

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
<table style="width:100%;margin: 10px;">
  <tbody>
<%
String logDirPath = application.getInitParameter("logDir");
if (logDirPath == null) {
%>
    <tr>
      <td>
        You need to provide a value for the context parameter
        <code>&quot;logDir&quot;</code>
      </td>
    </tr>
<% 
} else {
    java.io.File logDir = new java.io.File(logDirPath);
    if (logDir.isDirectory() == false) {
%>
    <tr>
      <td>
        Context parameter logDir needs to be set to a directory.
        Currently set to &quot;<%=logDirPath%>&quot;
      </td>
    </tr>
<%
    } else {
        String[] projectDirs = logDir.list(new java.io.FilenameFilter() {
            public boolean accept(File dir, String name) {
                return (new File(dir, name).isDirectory());
            }
        });

        if (projectDirs.length == 0) {
%>
    <tr>
      <td>
        no project directories found under &quot;<%=logDirPath%>&quot;
      </td>
    </tr>
<%
        } else {
            Comparator c = new Comparator() {
                public int compare(Object o1, Object o2) {
                        File f1 = new File((String) o1);
                        File f2 = new File((String) o2);
                        return f1.compareTo(f2);
                }
                public boolean equals(Object obj) {
                        return false;
                }
            };
            Arrays.sort(projectDirs, c);
            
            Info project = null;
            for (int i = 0; i < projectDirs.length; i++) {
                project = new Info(logDir, projectDirs[i]);
%>
    <tr onmouseover="over(this);" onmouseout="out(this);">
      <td class="status-<%= project.getStatus().getImportance() %>">
        <div class="<%= (project.failed() ? "broken" : "good") %>">
          <div>
          <table>
            <tbody>
              <tr>
                <td class="play" rowspan="2">
                  <a href="#" onclick="callServer('<%= jmxURLPrefix.toExternalForm() + project.project%>');">
                  </a>
                </td>
                <td class="left">
                  <a href="buildresults/<%=project.project%>"><%= project.project %></a>
                </td>
                <td class="right"><%= project.getLabel()%></td>
              </tr>
              <tr>
                <td class="left status-<%= project.getStatus().getImportance() %>">
                  <%= project.getStatus()%> <em>(<%= project.getStatusSince() %>)</em>
                </td>
                <td class="right"><%= project.getLastSuccessfulBuildTime() %></td>
              </tr>
            </tbody>
          </table>
          </div>
        </div>
      </td>
    </tr>
<%
            }
        }
    }
}
%>
  </tbody>
</table>