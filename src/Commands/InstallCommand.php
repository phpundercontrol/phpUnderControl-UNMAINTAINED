<?php
/**
 * This file is part of phpUnderControl.
 * 
 * PHP Version 5.2.4
 *
 * Copyright (c) 2007-2008, Manuel Pichler <mapi@phpundercontrol.org>.
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
 * @category  QualityAssurance
 * @package   Commands
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

/**
 * Command implementation for the install mode.
 *
 * @category  QualityAssurance
 * @package   Commands
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucInstallCommand extends phpucAbstractCommand
{
    /**
     * List of new files.
     *
     * @type array<string>
     * @var array(string=>string) $installFiles
     */
    private $installFiles = array(
        '/dashboard.jsp',
        '/favicon.ico',
        '/footer.jsp',
        '/header.jsp',
        '/metrics.cewolf.jsp',
        '/phpcs.jsp',
        '/phpunit.jsp',
        '/phpunit-pmd.jsp',
        '/servertime.jsp',
        '/css/php-under-control.css',
        '/css/SyntaxHighlighter.css',
        '/images/php-under-control/dashboard-broken-left.png',
        '/images/php-under-control/dashboard-broken-right.png',
        '/images/php-under-control/dashboard-good-left.png',
        '/images/php-under-control/dashboard-good-right.png',
        '/images/php-under-control/error.png',
        '/images/php-under-control/failed.png',
        '/images/php-under-control/header-center.png',
        '/images/php-under-control/header-left-logo.png',
        '/images/php-under-control/info.png',
        '/images/php-under-control/skipped.png',
        '/images/php-under-control/success.png',
        '/images/php-under-control/tab-active.png',
        '/images/php-under-control/tab-inactive.png',
        '/images/php-under-control/warning.png',
        '/js/shBrushPhp.js',
        '/js/shCore.js',
        '/js/effects.js',
        '/js/prototype.js',
        '/js/scriptaculous.js',
        '/xsl/phpcs.xsl',
        '/xsl/phpcs-details.xsl',
        '/xsl/phpdoc.xsl',
        '/xsl/phphelper.xsl',
        '/xsl/phpunit.xsl',
        '/xsl/phpunit-details.xsl',
        '/xsl/phpunit-pmd.xsl',
        '/xsl/phpunit-pmd-details.xsl',
    );
    
    /**
     * List of modified files.
     *
     * @type array<string>
     * @var array(string=>string) $modifiedFiles
     */
    private $modifiedFiles = array(
        '/index.jsp',
        '/main.jsp',
        '/metrics.jsp',
        '/xsl/buildresults.xsl',
        '/xsl/errors.xsl',
        '/xsl/header.xsl',
        '/xsl/modifications.xsl',
    );
    
    /**
     * Creates all command specific {@link phpucTaskI} objects.
     * 
     * @return array(phpucTaskI)
     */
    protected function doCreateTasks()
    {
        $tasks = array();
        
        $tasks[] = new phpucCruiseControlTask( $this->args );
        $tasks[] = new phpucModifyFileTask( $this->args, $this->modifiedFiles );
        $tasks[] = new phpucCreateFileTask( $this->args, $this->installFiles );
        
        return $tasks;
    }
}