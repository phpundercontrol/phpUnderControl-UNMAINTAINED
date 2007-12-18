<?php
/**
 * This file is part of phpUnderControl.
 *
 * Copyright (c) 2007, Manuel Pichler <mapi@manuel-pichler.de>.
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
 * @package   Tasks
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

/**
 * Settings for the php unit tool.
 *
 * @package   Tasks
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 * 
 * @property-read boolean $metrics  Enable metrics support?
 * @property-read boolean $coverage Enable coverage support?
 */
class phpucPhpUnitTask extends phpucAbstractPearTask
{
    /**
     * Minimum code sniffer version.
     */
    const PHP_UNIT_VERSION = '3.2.0';
    
    /**
     * The ctor takes the PEAR install dir as an optional argument.
     * 
     * @param phpucConsoleArgs $args The command line arguments.
     */
    public function __construct( phpucConsoleArgs $args )
    {
        parent::__construct( 'phpunit', $args );
        
        $this->properties['metrics']  = true;
        $this->properties['coverage'] = true;
    }
    
    /**
     * Creates the coverage build directory.
     *
     * @return void
     * @throws phpucExecuteException If the execution fails.
     */
    public function execute()
    {
        echo 'Performing PHPUnit task.' . PHP_EOL;
        
        $installDir  = $this->args->getArgument( 'cc-install-dir' );
        $projectName = $this->args->getOption( 'project-name' );
        $projectPath = sprintf( '%s/projects/%s', $installDir, $projectName );
        
        printf( 
            '  1. Creating coverage dir: project/%s/build/coverage%s', 
            $projectName, 
            PHP_EOL
        );
        mkdir( $projectPath . '/build/coverage' );
        
        printf( 
            '  2. Modifying build file:  project/%s/build.xml%s', 
            $projectName, 
            PHP_EOL 
        );
        
        $logs  = ' --log-xml ${basedir}/build/logs/phpunit.xml';
        $logs .= ' --log-pmd ${basedir}/build/logs/phpunit.pmd.xml ';
        if ( $this->metrics === true )
        {
            $logs .= ' --log-metrics ${basedir}/build/logs/phpunit.metrics.xml';
        }
        $coverage = '';
        if ( $this->coverage === true )
        {
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
        
        echo '  3. Modifying config file: config.xml' . PHP_EOL;
        
        $configFile    = new phpucConfigFile( $installDir . '/config.xml' );
        $configProject = $configFile->getProject( $projectName );
        $publisher     = $configProject->createArtifactsPublisher();
        
        $publisher->dir          = 'projects/${project.name}/build/coverage';
        $publisher->subdirectory = 'coverage';
        
        $configFile->save();
        
        echo PHP_EOL;
    }
    
    /**
     * Validates the existing code sniffer version.
     *
     * @return void
     */
    protected function doValidate()
    {
        ob_start();
        system( "{$this->executable} --version" );
        $retval = ob_get_contents();
        ob_end_clean();

        if ( preg_match( '/\s+([0-9\.]+(RC[0-9])?)/', $retval, $match ) === 0 )
        {
            echo 'WARNING: Cannot identify PHPUnit version.' . PHP_EOL;
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
            printf(
                'NOTICE: The identified version %s doesn\'t support metrics.%s' .
                'You may switch to PHPUnit %s for cooler features.%s',
                $version,
                PHP_EOL,
                self::PHP_UNIT_VERSION,
                PHP_EOL
            );
            $this->properties['metrics'] = false;
        }

        // Check xdebug installation
        if ( extension_loaded( 'xdebug' ) === false )
        {
            printf(
                'NOTICE: The xdebug extension is not installed. For coverage%s' .
                'you must install xdebug with the following command:%s' .
                '  pecl install xdebug%s',
                PHP_EOL,
                PHP_EOL,
                PHP_EOL
            );
            $this->properties['coverage'] = false;
        }
    }
}
