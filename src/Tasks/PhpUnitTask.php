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
 * @package   Tasks
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

/**
 * Settings for the php unit tool.
 *
 * @category  QualityAssurance
 * @package   Tasks
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 * 
 * @property-read boolean $metrics  Enable metrics and coverage support?
 */
class phpucPhpUnitTask extends phpucAbstractPearTask
{
    /**
     * Minimum code sniffer version.
     */
    const PHP_UNIT_VERSION = '3.2.0';
    
    /**
     * Constructs a new phpunit task.
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->properties['metrics']  = true;
    }
    
    /**
     * Creates the coverage build directory.
     *
     * @return void
     * @throws phpucExecuteException If the execution fails.
     */
    public function execute()
    {
        $out = phpucConsoleOutput::get();
        $out->writeLine( 'Performing PHPUnit task.' );
        
        $installDir  = $this->args->getArgument( 'cc-install-dir' );
        $projectName = $this->args->getOption( 'project-name' );
        $projectPath = sprintf( '%s/projects/%s', $installDir, $projectName );
        
        $out->startList();
        
        $out->writeListItem(
            'Creating coverage dir: project/{1}/build/coverage', $projectName
        );
        
        mkdir( $projectPath . '/build/coverage', 0755, true );
        
        $out->writeListItem(
            'Modifying build file:  project/{1}/build.xml', $projectName
        );
        $logs  = ' --log-xml ${basedir}/build/logs/phpunit.xml';
        if ( $this->metrics === true )
        {
            $logs .= ' --log-pmd ${basedir}/build/logs/phpunit.pmd.xml ';
            $logs .= ' --log-metrics ${basedir}/build/logs/phpunit.metrics.xml';
            $logs .= ' --coverage-xml  ${basedir}/build/logs/phpunit.coverage.xml';
            $logs .= ' --coverage-html ${basedir}/build/coverage';
        }
        
        $buildFile = new phpucBuildFile( $projectPath . '/build.xml' );
        
        $buildTarget              = $buildFile->createBuildTarget( 'phpunit' );
        $buildTarget->executable  = $this->executable;
        $buildTarget->failonerror = true;
        $buildTarget->argLine     = sprintf(
            '%s %s %s/%s',
            $logs,
            $this->args->getOption( 'test-case' ),
            $this->args->getOption( 'test-dir' ),
            $this->args->getOption( 'test-file' )
        );
        
        $buildFile->save();
        
        $out->writeListItem( 'Modifying config file: config.xml' );
        
        $configFile    = new phpucConfigFile( $installDir . '/config.xml' );
        $configProject = $configFile->getProject( $projectName );
        $publisher     = $configProject->createArtifactsPublisher();
        
        $publisher->dir          = 'projects/${project.name}/build/coverage';
        $publisher->subdirectory = 'coverage';
        
        if ( $this->artifacts )
        {
            $publisher->dest = 'artifacts/${project.name}';
        }
        else
        {
            $publisher->dest = 'logs/${project.name}';
        }
        
        $configFile->save();
        
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
        parent::register( $def );
        
        $def->addOption(
            'project',
            'n',
            'without-phpunit',
            'Disable PHPUnit support.',
            false
        );
        $def->addOption(
            'project',
            't',
            'test-dir',
            'The test directory in the project.',
            true,
            null,
            false
        );
        $def->addOption(
            'project',
            'a',
            'test-case',
            'Name of the test case class.',
            true,
            null,
            false
        );
        $def->addOption(
            'project',
            'l',
            'test-file',
            'Name of the test case file.',
            true,
            null,
            false
        );
    }
    
    /**
     * Validates the existing code sniffer version.
     *
     * @return void
     */
    protected function doValidate()
    {
        $cwd = getcwd();
        
        $binary = basename( $this->executable );
        
        if ( ( $execdir = dirname( $this->executable ) ) !== '.' )
        {
            chdir( $execdir );

            if ( phpucFileUtil::getOS() === phpucFileUtil::OS_UNIX )
            {
                $binary = "./{$binary}";
            }
        }
            
        $regexp = '/PHPUnit\s+([0-9\.]+(RC[0-9])?)/';
        $retval = exec( escapeshellcmd( "{$binary} --version" ) );
        
        chdir( $cwd );

        if ( preg_match( '/\s+([0-9\.]+(RC[0-9])?)/', $retval, $match ) === 0 )
        {
            phpucConsoleOutput::get()->writeLine(
                'WARNING: Cannot identify PHPUnit version.'
            );
            // Assume valid version
            $version = self::PHP_UNIT_VERSION;
        }
        else
        {
            $version = $match[1];
        }
        
        // Check version and inform user
        if ( version_compare( $version, self::PHP_UNIT_VERSION ) < 0 )
        {
            phpucConsoleOutput::get()->writeLine(
                'NOTICE: The identified version {1} doesn\'t support metrics.',
                $version
            );
            phpucConsoleOutput::get()->writeLine(
                'You may switch to PHPUnit {1} for cooler features.', 
                self::PHP_UNIT_VERSION 
            );
        }

        // Check xdebug installation
        if ( extension_loaded( 'xdebug' ) === false )
        {
            phpucConsoleOutput::get()->writeLine(
                'NOTICE: The xdebug extension is not installed. For coverage'
            );
            phpucConsoleOutput::get()->writeLine(
                'you must install xdebug with the following command:'
            );
            phpucConsoleOutput::get()->writeLine(
                '  pear install pecl/xdebug'
            );
            
            $this->properties['metrics'] = false;
        }
    }
    
    /**
     * Must return the name of the used cli tool.
     *
     * @return string
     */
    protected function getCliToolName()
    {
        return 'phpunit';
    }
}
