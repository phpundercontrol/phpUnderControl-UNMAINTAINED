<?php
/**
 * This file is part of phpUnderControl.
 * 
 * PHP Version 5.2.4
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
 * Settings for the php code sniffer tool.
 *
 * @category  QualityAssurance
 * @package   Tasks
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucPhpCodeSnifferTask extends phpucAbstractPearTask
{
    /**
     * Minimum code sniffer version.
     */
    const CODE_SNIFFER_VERSION = '1.0.0RC3';
    
    /**
     * The ctor takes the PEAR install dir as an optional argument.
     * 
     * @param phpucConsoleArgs $args The command line arguments.
     */
    public function __construct( phpucConsoleArgs $args )
    {
        parent::__construct( 'phpcs', $args );
    }
    
    /**
     * Does nothing.
     *
     * @return void
     */
    public function execute()
    {
        $out = phpucConsoleOutput::get();
        $out->writeLine( 'Performing PHP_CodeSniffer task.' );
        
        $projectName = $this->args->getOption( 'project-name' );
        $projectPath = sprintf(
            '%s/projects/%s', 
            $this->args->getArgument( 'cc-install-dir' ), 
            $projectName
        );
        
        $out->startList();
        $out->writeListItem( 
            'Modifying build file: project/{1}/build.xml', $projectName
        );
        
        $buildFile = new phpucBuildFile( $projectPath . '/build.xml', $projectName );
        
        $buildTarget = $buildFile->createBuildTarget( 'php-codesniffer' );
        
        $buildTarget->executable = $this->executable;
        $buildTarget->output     = '${basedir}/build/logs/checkstyle.xml';
        $buildTarget->argLine    = sprintf(
            '--report=checkstyle --standard=%s %s',
            $this->args->getOption( 'coding-guideline' ),
            $this->args->getOption( 'source-dir' )
        );
        
        $buildFile->save();
        
        $out->writeLine();
    }
    
    /**
     * Validates the existing code sniffer version.
     *
     * @return void
     */
    protected function doValidate()
    {
        $retval = exec( escapeshellcmd( "{$this->executable} --version" ) );

        if ( preg_match( '/version\s+([0-9\.]+(RC[0-9])?)/', $retval, $match ) === 0 )
        {
            phpucConsoleOutput::get()->writeLine(
                'WARNING: Cannot identify PHP_CodeSniffer version.'
            );
            // Assume valid version
            $version = self::CODE_SNIFFER_VERSION;
        }
        else
        {
            $version = $match[1];
        }
        
        if ( version_compare( $version, self::CODE_SNIFFER_VERSION ) < 0 )
        {
            throw new phpucValidateException(
                sprintf( 
                    'PHP_CodeSniffer version %s or higher required. Given version is "%s".',
                    self::CODE_SNIFFER_VERSION,
                    $version
                )
            );
        }
    }
}