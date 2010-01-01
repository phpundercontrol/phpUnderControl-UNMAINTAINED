/**
 * This file is part of phpUnderControl.
 *
 * Copyright (c) 2007-2010, Manuel Pichler <mapi@phpundercontrol.org>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Manuel Pichler nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 * 
 * @category QualityAssurance
 * @author Manuel Pichler <mapi@phpundercontrol.org>
 * @category 2007-2010 Manuel Pichler. All rights reserved. 
 * @version SVN: $Id$
 */

package org.phpundercontrol.dashboard;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileReader;
import java.io.IOException;
import java.text.ParseException;
import java.util.Date;

import net.sourceforge.cruisecontrol.BuildInfo;
import net.sourceforge.cruisecontrol.LogFile;

/**
 * Data object for a single project.
 * 
 * @category QualityAssurance
 * @author Manuel Pichler <mapi@phpundercontrol.org>
 * @category 2007-2010 Manuel Pichler. All rights reserved. 
 * @version SVN: $Id$
 */
public class ProjectInfo implements Comparable<ProjectInfo> {
	/**
	 * One day in milliseconds.
	 */
	public static final int ONE_DAY = 1000 * 60 * 60 * 24;

	/**
	 * Current time.
	 */
	private Date now = new Date();

	/**
	 * Info of the latest build.
	 */
	private BuildInfo latest;
	
	/**
	 * Info of the last successful build. 
	 */
	private BuildInfo lastSuccessful;

	/**
	 * Current build status for the current project.
	 */
	private BuildStatus status;
	private Date statusSince;
	private String project;
	private String statusDescription;
	private BuildStatusMap statusMap;
	private TimeFormater formater;

	/**
	 * Constructs a new project info instance.
	 * 
	 * @param formater The used time formater.
	 * @param fileName Name of the build status file.
	 * @param logsDir A file instance for the directory where cc stores the logs.
	 * @param project The name of the current project.
	 * @throws ParseException
	 * @throws IOException
	 */
	public ProjectInfo(TimeFormater formater, 
					   String fileName, 
					   File logsDir, 
					   String project) throws ParseException, IOException {

		this.project  = project;
		this.formater = formater;

		this.statusMap = new BuildStatusMap();

		File projectLogDir = new File(logsDir, project);

		LogFile latestLog = LogFile.getLatestLogFile(projectLogDir);
		if (latestLog != null) {
			this.latest = new BuildInfo(latestLog);
		}

		LogFile successLog = LogFile.getLatestSuccessfulLogFile(projectLogDir);
		if (successLog != null) {
			this.lastSuccessful = new BuildInfo(successLog);
		}

		File statusFile = new File(projectLogDir, fileName);
		BufferedReader reader = null;
		try {
			reader = new BufferedReader(new FileReader(statusFile));
			this.statusDescription = reader.readLine().replaceAll(" since", "");

			this.status      = this.statusMap.get(this.statusDescription);
			this.statusSince = new Date(statusFile.lastModified());
		} catch (Exception e) {
			this.status      = this.statusMap.get(null);
			this.statusSince = this.now;
		} finally {
			if (reader != null) {
				reader.close();
			}
		}
	}

	/**
	 * Returns the name of the context project.
	 * 
	 * @return The project name.
	 */
	public String getProject() {
		return this.project;
	}

	/**
	 * Returns the last build time for this project.
	 * 
	 * @return Formated build time.
	 */
	public String getLastBuildTime() {
		return this.getTime(this.latest);
	}

	/**
	 * Returns the build time of the last successful build for this project.
	 * 
	 * @return The formated build time
	 */
	public String getLastSuccessfulBuildTime() {
		return this.getTime(this.lastSuccessful);
	}

	/**
	 * Returns the formated build date for the given build.
	 * 
	 * @param build The build info instance.
	 * @return A string representation.
	 */
	private String getTime(BuildInfo build) {
		return build != null ? this.format(build.getBuildDate()) : "";
	}

	/**
	 * Formats the given date object into a localized string
	 * 
	 * @param date The date instance.
	 * @return The formated localized string.
	 */
	private String format(Date date) {
		if (date == null) {
			return "";
		}

		if ((now.getTime() < date.getTime())) {
			return this.formater.formatDateTime(date);
		}

		if ((now.getTime() - date.getTime()) < ONE_DAY) {
			return this.formater.formatTime(date);
		}

		return this.formater.formatDate(date);
	}

	/**
	 * ...
	 * 
	 * @return
	 */
	public String getStatusSince() {
		return this.statusSince != null ? format(this.statusSince) : "?";
	}

	/**
	 * Returns <b>true</b> when the last build was not successful or there is
	 * no previous build.
	 * 
	 * @return true or false.
	 */
	public boolean failed() {
		return this.latest == null || !this.latest.isSuccessful();
	}

	/**
	 * Returns the current build status for this project.
	 * 
	 * @return The build status.
	 */
	public BuildStatus getStatus() {
		return this.status;
	}

	/**
	 * Compares this project info with given project instance. First this method
	 * compares the project names, when both names are equal it uses the status
	 * since property.
	 * 
	 * @return -1, 1 or 0
	 */
	public int compareTo(ProjectInfo other) {
		int order = this.project.compareTo(other.project);
		if (order != 0) {
			return order;
		}
		return (int) (this.statusSince.getTime() - other.statusSince.getTime());
	}

	/**
	 * Returns the label for this project.
	 * 
	 * @return The project label.
	 */
	public String getLabel() {
		return this.lastSuccessful != null ? this.lastSuccessful.getLabel() : " ";
	}
}