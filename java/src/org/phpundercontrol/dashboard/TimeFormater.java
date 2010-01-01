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

import java.text.DateFormat;
import java.util.Date;
import java.util.Locale;

/**
 * Utility class used to created formated build time/date strings. 
 * 
 * @category QualityAssurance
 * @author Manuel Pichler <mapi@phpundercontrol.org>
 * @category 2007-2010 Manuel Pichler. All rights reserved. 
 * @version SVN: $Id$
 */
public class TimeFormater {

	/**
	 * Formats a date and time string.
	 */
	private final DateFormat dateTime;
	
	/**
	 * Formats a date only string.
	 */
	private final DateFormat dateOnly;
	
	/**
	 * Formats a time only string.
	 */
	private final DateFormat timeOnly;
	
	/**
	 * Constructs a new formater for the given locale.
	 * 
	 * @param locale The client locale instance.
	 */
	public TimeFormater(Locale locale) {
		this.dateTime = DateFormat.getDateTimeInstance(DateFormat.SHORT, DateFormat.SHORT, locale);
		this.dateOnly = DateFormat.getDateInstance(DateFormat.SHORT, locale);
		this.timeOnly = DateFormat.getTimeInstance(DateFormat.SHORT, locale);
	}
	
	/**
	 * Formats the given date into a date+time-string.
	 * 
	 * @param date The date instance.
	 * @return The formated string.
	 */
	public String formatDateTime(Date date) {
		return this.dateTime.format(date);
	}
	
	/**
	 * Formats the given date into a date-string.
	 * 
	 * @param date The date instance.
	 * @return The formated string.
	 */
	public String formatDate(Date date) {
		return this.dateOnly.format(date);
	}
	
	/**
	 * Formats the given date into a time-string.
	 * 
	 * @param date The date instance.
	 * @return The formated string.
	 */
	public String formatTime(Date date) {
		return this.timeOnly.format(date);
	}
}
