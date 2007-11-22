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
 * @package phpUnderControl
 */

/**
 * Implementation mode of the example mode.
 *
 * @package   phpUnderControl
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   $Id: ExampleMode.php 1689 2007-11-21 15:22:47Z mapi $
 */
class pucExampleMode extends pucAbstractMode
{
    /**
     * List of example files.
     *
     * @type array<string>
     * @var array(string=>string) $exampleFiles
     */
    private $exampleFiles = array(
        'src/Math.php'        =>  null,
        'tests/MathTest.php'  =>  null,
    );
    
    /**
     * Executes this mode task.
     *
     * @return void
     */
    public function execute()
    {
        $ccsetting = $this->getCCSetting();
        $project   = $ccsetting->exampleName;

        printf( '%sCreating required project directories.%s', PHP_EOL, PHP_EOL );
        $this->createDirectories( $project );
        
        printf( '%sCreating example files.%s', PHP_EOL, PHP_EOL );
        $this->copyExampleFiles( $project );
        
        printf( '%sCreating project build file "%s/build.xml".%s', PHP_EOL, $project, PHP_EOL );
        $this->createBuildFile( $project );
        
        printf( '%sPreparing CruiseControl "%s/config.xml.%s', PHP_EOL, $ccsetting->ccInstallDir, PHP_EOL );
        $this->prepareConfig( $project );
    }
    
    /**
     * Creates the required directory structure.
     *
     * @param string $project The project name.
     * 
     * @return void
     */
    private function createDirectories( $project )
    {
        $ccs  = $this->getCCSetting();
        $path = $ccs->ccInstallDir;
        
        $projectDir      = sprintf( '%s/projects/%s', $path, $project );
        $projectInput    = sprintf( '%s/projects/%s/source', $path, $project );
        $projectSrc      = sprintf( '%s/projects/%s/source/src', $path, $project );
        $projectTests    = sprintf( '%s/projects/%s/source/tests', $path, $project );
        $projectBuild    = sprintf( '%s/projects/%s/build', $path, $project );
        $projectLogs     = sprintf( '%s/projects/%s/build/logs', $path, $project );
        $projectApi      = sprintf( '%s/projects/%s/build/api', $path, $project );
        $projectCoverage = sprintf( '%s/projects/%s/build/coverage', $path, $project );

        printf( '  1. Creating CruiseControl project dir "projects/%s".' . PHP_EOL, $project );
        mkdir( $projectDir );
        printf( '  2. Creating Project input dir "%s/source".' . PHP_EOL, $project );
        mkdir( $projectInput );
        printf( '  3. Creating Project source dir "%s/source/src".' . PHP_EOL, $project );
        mkdir( $projectSrc );
        printf( '  4. Creating Project test dir "%s/source/tests".' . PHP_EOL, $project );
        mkdir( $projectTests );
        printf( '  5. Creating Project build dir "%s/build".' . PHP_EOL, $project );
        mkdir( $projectBuild );
        printf( '  6. Creating Project log dir "%s/build/logs"' . PHP_EOL, $project );
        mkdir( $projectLogs );
        printf( '  7. Creating Project coverage dir "%s/build/coverage"' . PHP_EOL, $project );
        mkdir( $projectCoverage );
        printf( '  7. Creating Project documentation dir "%s/build/api"' . PHP_EOL, $project );
        mkdir( $projectApi );
    }
    
    /**
     * Copies the example files.
     *
     * @param string $project The project name.
     * 
     * @return void
     */ 
    private function copyExampleFiles( $project )
    {
        $ccs  = $this->getCCSetting();
        $path = sprintf( '%s/projects/%s/source/', $ccs->ccInstallDir, $project );
        
        foreach ( $this->exampleFiles as $filename => $content )
        {
            if ( $content === null )
            {
                $content = $this->loadFileContent(
                    dirname( __FILE__ ) . '/../example/' . $filename
                );
                
                $this->exampleFiles[$filename] = $content;
            }
            
            printf( '  Creating file "%s/source/%s".' . PHP_EOL, $project, $filename );
            file_put_contents( $path . $filename, base64_decode( $content ) );
        }
    }
    
    /**
     * Create the ant build file.
     *
     * @param string $project The project name.
     * 
     * @return void
     */
    private function createBuildFile( $project )
    {
        $xml = sprintf(
            '<?xml version="1.0"?>
<project name="%s" default="build" basedir=".">
',  $project
        );
        
        $targets = array();
        
        foreach ( $this->getToolSettings() as $setting )
        {
            $targets[] = $setting->cliTool;
            
            $xml .= $setting->generate();
        }
        
        $xml .= sprintf( '
  <target name="build" depends="%s" />
  
</project>',
            implode( ',', $targets )
        );
        
        $ccs  = $this->getCCSetting();
        $path = sprintf( '%s/projects/%s/build.xml', $ccs->ccInstallDir, $project );
        
        file_put_contents( $path, $xml );
    }
    
    /**
     * Prepares the CruiseControl config.xml file.
     *
     * @param string $project The project name.
     * 
     * @return void
     */
    private function prepareConfig( $project )
    {
        $ccs  = $this->getCCSetting();
        $path = sprintf( '%s/config.xml', $ccs->ccInstallDir );
        
        if ( count( $ant = glob( sprintf( '%s/apache-ant*', $ccs->ccInstallDir ) ) ) === 0 )
        {
            echo 'ERROR: Cannot locate ant directory.' . PHP_EOL;
            exit( 1 );
        }
        $ant = basename( array_pop( $ant ) );
        
        
        echo '  1. Loading "config.xml" data.' . PHP_EOL;
        $config = new DOMDocument();
        $config->load( $path );
        
        $xpath = new DOMXPath( $config );
        if ( $xpath->query( sprintf( '//project[@name="%s"]', $project ) )->length !== 0 )
        {
            echo '  Project is already configured.' . PHP_EOL;
            return;
        }
        
        @unlink( "{$path}.orig" );
        
        echo '  2. Backup original "config.xml" as "config.xml.orig".' . PHP_EOL;
        copy( $path, "{$path}.orig" );
        
        echo '  3. Creating new project configuration.' . PHP_EOL;
        $newConf = new DOMDocument();
        $newConf->loadXML(
            sprintf( '
  <project name="%s" buildafterfailed="false">
    <listeners>
      <currentbuildstatuslistener file="logs/${project.name}/status.txt"/>
    </listeners>

    <modificationset>
      <alwaysbuild />
    </modificationset>

    <schedule interval="60">
      <ant anthome="%s" 
           buildfile="projects/${project.name}/build.xml"/>
    </schedule>

    <log dir="logs/${project.name}">
      <merge dir="projects/${project.name}/build/logs/"/>
    </log>

    <publishers>
      <currentbuildstatuspublisher file="logs/${project.name}/buildstatus.txt"/>
      <artifactspublisher dir="projects/${project.name}/build/coverage"
                          dest="logs/${project.name}"
                          subdirectory="coverage" />
      <artifactspublisher dir="projects/${project.name}/build/api"
                          dest="logs/${project.name}"
                          subdirectory="api" />
    </publishers>
  </project>',
                $project,
                $ant
            )
        );
        
        $newConf = $config->importNode( $newConf->documentElement, true );
        $config->documentElement->appendChild( $newConf );
        
        $config->save( $path );
    }
    
    /**
     * Loads the file content.
     *
     * @param string $file The file name.
     * 
     * @return string base64 encoded content
     */
    private function loadFileContent( $file )
    {
        return base64_encode( file_get_contents( $file ) );
    }
}