<?php
/**
 * This file is part of phpUnderControl.
 * 
 * PHP Version 5
 *
 * Copyright (c) 2007-2008, Manuel Pichler <mapi@manuel-pichler.de>.
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
 * @package   Tasks
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

/**
 * Performs a project checkout.
 *
 * @category  QualityAssurance
 * @package   Tasks
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucCheckoutTask extends phpucAbstractTask implements phpucConsoleExtensionI
{
    public function execute()
    {
        $out = phpucConsoleOutput::get();
        $out->writeLine( 'Performing checkout task.' );
        
        // Get current working dir
        $cwd = getcwd();
        
        $projectPath = sprintf(
            '%s/projects/%s',
            $this->args->getArgument( 'cc-install-dir' ),
            $this->args->getOption( 'project-name' )
        );
        
        // Switch working dir to the CruiseControl project directory
        chdir( $projectPath );
        
        $checkout = null;
        switch ( $this->args->getOption( 'version-control' ) )
        {
            case 'svn':
                $checkout = new phpucSubversionCheckout();
                break;
                
            case 'cvs':
                $checkout = new phpucCvsCheckout();
                break;
        }
        
        
        
        $checkout->url  = $this->args->getOption( 'version-control-url' );
        $checkout->dest = $this->args->getOption( 'destination' );
        $checkout->checkout();
        
        chdir( $cwd );
        
        $out->writeLine();
    }
    
    /**
     * Callback method that registers the interested commands or options. 
     *
     * @param phpucConsoleInputDefinition $def The input definition container.
     * 
     * @return void
     */
    public function register( phpucConsoleInputDefinition $def )
    {
        $def->addOption(
            'project',
            'y',
            'version-control',
            'The used version control system.',
            array( 'svn', 'cvs' ),
            null,
            true
        );
        $def->addOption(
            'project',
            'x',
            'version-control-url',
            'The version control system project url.',
            true,
            null,
            true
        );
        $def->addOption(
            'project',
            'u',
            'username',
            'Optional username for the version control system.',
            true
        );
        $def->addOption(
            'project',
            'p',
            'password',
            'Optional password for the version control system.',
            true
        );
        $def->addOption(
            'project',
            'd',
            'destination',
            'A destination directory for the source code checkout. Default is "source".',
            true,
            'source',
            true
        );
        $def->addOption(
            'project',
            'm',
            'module',
            'A CVS project module.',
            true
        );
    }
}