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

import java.util.HashMap;
import java.util.Map;

import net.sourceforge.cruisecontrol.ProjectState;

/**
 * Map class that returns the correct {@link BuildStatus} instance for a project
 * description.
 * 
 * @category QualityAssurance
 * @author Manuel Pichler <mapi@phpundercontrol.org>
 * @category 2007-2010 Manuel Pichler. All rights reserved. 
 * @version SVN: $Id$
 */
public class BuildStatusMap {
	
	/**
	 * Important build status
	 */
	private static final String IMPORTANT = "important";
	
	/**
	 * Normal build status.
	 */
	private static final String NORMAL = "normal";
	
	/**
	 * Dull build status.
	 */
	private static final String DULL = "dull";
	
	/**
	 * Mapping between CruiseControl project states and view importance strings.
	 */
	private Map<String, BuildStatus> map = new HashMap<String, BuildStatus>();
	
	/**
	 * Status for for unknown values.
	 */
	private BuildStatus unknown = new BuildStatus(null, DULL);

	/**
	 * Constructs a new status map instance.
	 */
	public BuildStatusMap() {
		this.add(ProjectState.PUBLISHING, IMPORTANT);
		this.add(ProjectState.MODIFICATIONSET, IMPORTANT);
		this.add(ProjectState.BUILDING, IMPORTANT);
		this.add(ProjectState.MERGING_LOGS, IMPORTANT);
		this.add(ProjectState.BOOTSTRAPPING, NORMAL);
		this.add(ProjectState.QUEUED, NORMAL);
		this.add(ProjectState.WAITING, DULL);
		this.add(ProjectState.IDLE, DULL);
		this.add(ProjectState.PAUSED, DULL);
		this.add(ProjectState.STOPPED, DULL);
	}

	/**
	 * Adds a new {@link BuildStatus} for the given state and importance string.
	 * 
	 * @param state The CruiseControl project state instance.
	 * @param importance The importance representation for the project state.
	 */
	public void add(ProjectState state, String importance) {
		this.map.put(state.getDescription(), new BuildStatus(state, importance));
	}

	/**
	 * Returns the build statis for the given status description.
	 * 
	 * @param statusDescription The project status description.
	 * @return The build status object.
	 */
	public BuildStatus get(String statusDescription) {
		if (this.map.containsKey(statusDescription)) {
			return this.map.get(statusDescription);
		}
		return this.unknown;
	}
}
