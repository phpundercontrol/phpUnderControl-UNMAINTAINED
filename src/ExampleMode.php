<?php
/**
 * This file is part of phpUnderControl.
 *
 * phpUnderControl is free software; you can redistribute it and/or 
 * modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * phpUnderControl is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with phpUnderControl; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * 
 * @package phpUnderControl
 */

/**
 * Implementation mode of the example mode.
 *
 * @package   phpUnderControl
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007 Manuel Pichler. All rights reserved.
 * @license   GPL http://www.gnu.org/licenses/gpl-3.0.txt
 * @version   $Id$
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
        
        $projectDir   = sprintf( '%s/projects/%s', $path, $project );
        $projectInput = sprintf( '%s/projects/%s/source', $path, $project );
        $projectSrc   = sprintf( '%s/projects/%s/source/src', $path, $project );
        $projectTests = sprintf( '%s/projects/%s/source/tests', $path, $project );
        $projectBuild = sprintf( '%s/projects/%s/build', $path, $project );
        $projectLogs  = sprintf( '%s/projects/%s/build/logs', $path, $project );

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

        $output = null;
        
        $tools = $this->getToolSettings();
        if ( count( $tools ) > 0 )
        {
            foreach ( $tools as $tool )
            {
                if ( $tool->outputDir !== null )
                {
                    $output = $tool->outputDir;
                    break; 
                }
            }
        }
        if ( $output !== null )
        {
            $outputApi      = sprintf( '%s/api', $output );
            $outputCoverage = sprintf( '%s/coverage', $output );
            
            printf( '  7. Creating API documentation dir "%s"' . PHP_EOL, $outputApi );
            mkdir( $outputApi );
            printf( '  8. Creating Coverage dir "%s"' . PHP_EOL, $outputCoverage );
            mkdir( $outputCoverage );
        }
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