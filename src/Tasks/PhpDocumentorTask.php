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
 * Settings for the php documentor tool.
 *
 * @package   Tasks
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucPhpDocumentorTask extends phpucAbstractPearTask
{
    /**
     * The ctor takes the PEAR install dir as an optional argument.
     *
     * @param phpucConsoleArgs $args The command line arguments.
     */
    public function __construct( phpucConsoleArgs $args )
    {
        parent::__construct( 'phpdoc', $args );
    }
    
    /**
     * Creates the api documentation build directory.
     *
     * @return void
     * @throws phpucExecuteException If the execution fails.
     */
    public function execute()
    {
        echo 'Performing PhpDocumentor task.' . PHP_EOL;
        
        $installDir  = $this->args->getArgument( 'cc-install-dir' );
        $projectName = $this->args->getOption( 'project-name' );
        $projectPath = sprintf( '%s/projects/%s', $installDir, $projectName );
        
        printf( 
            '  1. Creating api documentation dir: project/%s/build/api%s', 
            $projectName, 
            PHP_EOL
        );
        mkdir( $projectPath . '/build/api' );
        
        printf( 
            '  2. Modifying build file:           project/%s/build.xml%s', 
            $projectName, 
            PHP_EOL
        );
        
        $buildFile = new phpucBuildFile( $projectPath . '/build.xml' );
        
        $buildTarget             = $buildFile->createBuildTarget( 'php-documentor' );
        $buildTarget->executable = $this->executable;
        $buildTarget->logerror   = true;
        $buildTarget->argLine    = sprintf(
            '-ue on -t ${basedir}/build/api -d %s',
            $this->args->getOption( 'source-dir' )
        );
        
        $buildFile->save();
        
        echo '  3. Modifying config file:          config.xml' . PHP_EOL;
        
        $configFile    = new phpucConfigFile( $installDir . '/config.xml' );
        $configProject = $configFile->getProject( $projectName );
        $publisher     = $configProject->createArtifactsPublisher();
        
        $publisher->dir          = 'projects/${project.name}/build/api';
        $publisher->subdirectory = 'api';
        
        $configFile->save();

        echo PHP_EOL;
    }
}