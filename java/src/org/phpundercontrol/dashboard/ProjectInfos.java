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

import java.io.File;
import java.io.IOException;
import java.text.ParseException;
import java.util.ArrayList;
import java.util.Collections;
import java.util.Iterator;
import java.util.List;
import java.util.Locale;

/**
 * This class provides a simple list of all installed projects.
 * 
 * @author Manuel Pichler
 */
public class ProjectInfos implements Iterable<ProjectInfo> {

	/**
	 * The CruiseControl log directory. 
	 */
	private String logDirectory = null;
	
	/**
	 * Name of a CruiseControl build status file.
	 */
	private String fileName = null;
	
	/**
	 * The used time/date string formater.
	 */
	private TimeFormater formater;
	
	/**
	 * List of all project directories.
	 */
	private List<ProjectInfo> projects;
	
	/**
	 * Marks the given log source as valid.
	 */
	private boolean valid = true;
	
	/**
	 * Constructs a new project log directory instance.
	 * 
	 * @param logDirectory The cc log directory
	 * @param fileName Name of CruisreControl's build status file
	 * @param locale The received client locals
	 */
	public ProjectInfos(String logDirectory, String fileName, Locale locale)
		throws ParseException, IOException {
		
		this.logDirectory = logDirectory;
		this.fileName     = fileName;
		this.formater     = new TimeFormater(locale);
		
		this.loadProjectInfo();
	}
	
	/**
	 * Returns <b>true</b> when this is a valid result.
	 *  
	 * @return boolean
	 */
	public boolean isValid() {
		return this.valid;
	}
	
	/**
	 * This method will return <b>true</b> when no projects exists. 
	 * 
	 * @return boolean
	 */
	public boolean isEmpty() {
		return this.projects.isEmpty();
	}
	
	@Override
	public Iterator<ProjectInfo> iterator() {
		return this.projects.iterator();
	}

	/**
	 * Loads all project info objects from CruiseControl's log directory.
	 * 
	 * @throws ParseException
	 * @throws IOException
	 */
	private void loadProjectInfo() throws ParseException, IOException {
		this.projects = new ArrayList<ProjectInfo>();

		File file  = new File(this.logDirectory);
		this.valid = file.isDirectory();
		
		if (this.valid == false) {
			return;
		}
		
		String[] projectDirList = file.list(new DirectoryFilter());
		for (String project: projectDirList) {
			this.projects.add(new ProjectInfo(this.formater, this.fileName, file, project));
		}

		Collections.sort(this.projects);
	}
}