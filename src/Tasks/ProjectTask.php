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
 * @package    phpUnderControl
 * @subpackage Tasks
 * @author     Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright  2007 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/wiki/phpUnderControl
 */

/**
 * This task creates the base directory structure for a new project.
 *
 * @package    phpUnderControl
 * @subpackage Tasks
 * @author     Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright  2007 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/wiki/phpUnderControl
 */
class phpucProjectTask extends phpucAbstractTask
{
    /**
     * Validates that the required <cc-install-dir>/projects directory exists.
     * 
     * @return void
     * @throws phpucValidateException If the directory doesn't exist or the 
     *         a project for the given name already exists.
     */
    public function validate()
    {
        $installDir  = $this->args->getArgument( 'cc-install-dir' );
        $projectName = $this->args->getOption( 'project-name' );
        
        if ( !is_dir( $installDir . '/projects' ) )
        {
            throw new phpucValidateException(
                'Missing projects directory <cc-install-dir>/projects.'
            );
        }
        if ( is_dir( $installDir . '/projects/' . $projectName ) )
        {
            throw new phpucValidateException( 'Project directory already exists.' );
        }
    }
    
    public function execute()
    {
        $installDir  = $this->args->getArgument( 'cc-install-dir' );
        $projectName = $this->args->getOption( 'project-name' );
        $projectPath = sprintf( '%s/projects/%s', $installDir, $projectName );
        
        echo 'Performing project task.' . PHP_EOL;        
        
        printf( '  1. Creating project directory: projects/%s%s', $projectName, PHP_EOL );
        mkdir( $projectPath );
        
        printf( '  2. Creating source directory:  projects/%s/source%s', $projectName, PHP_EOL );
        mkdir( $projectPath . '/source' );
        
        printf( '  3. Creating build directory:   projects/%s/build%s', $projectName, PHP_EOL );
        mkdir( $projectPath . '/build' );
        
        printf( '  4. Creating log directory:     projects/%s/build/logs%s', $projectName, PHP_EOL );
        mkdir( $projectPath . '/build/logs' );
        
        printf( '  5. Creating build file:        projects/%s/build.xml%s', $projectName, PHP_EOL );
        
        $buildFile = new phpucBuildFile( $projectPath . '/build.xml', $projectName );
        $buildFile->save();
        
        echo '  6. Creating backup of file:    config.xml.orig' . PHP_EOL;
        @unlink( $installDir . '/config.xml.orig' );
        copy( $installDir . '/config.xml', $installDir . '/config.xml.orig' );
        
        echo '  7. Searching ant directory' . PHP_EOL;
        if ( count( $ant = glob( sprintf( '%s/apache-ant*', $installDir ) ) ) === 0 )
        {
            throw new phpucExecuteException( 'ERROR: Cannot locate ant directory.' );
        }
        $anthome = basename( array_pop( $ant ) );
        
        echo '  8. Modifying project file:     config.xml' . PHP_EOL;
        
        $config  = new phpucConfigFile( $installDir . '/config.xml' );
        $project = $config->createProject( $projectName );
        
        $project->interval = $this->args->getOption( 'schedule-interval' );
        $project->anthome  = $anthome;

        $config->save();
                
        echo PHP_EOL;
    }
}