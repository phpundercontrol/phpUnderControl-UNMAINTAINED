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
class pucInstallMode extends pucAbstractMode
{
    /**
     * List of new files.
     *
     * @type array<string>
     * @var array(string=>string) $installFiles
     */
    private $installFiles = array(
        'phpcs.jsp'                    =>  null,
        'phpunit-pmd.jsp'              =>  null,
        'xsl/phpcs.xsl'                =>  null,
        'xsl/phpcs-details.xsl'        =>  null,
        'xsl/phpdoc.xsl'               =>  null,
        'xsl/phphelper.xsl'            =>  null,
        'xsl/phpunit-pmd.xsl'          =>  null,
        'xsl/phpunit-pmd-details.xsl'  =>  null,
    );
    
    /**
     * List of modified files.
     *
     * @type array<string>
     * @var array(string=>string) $modifiedFiles
     */
    private $modifiedFiles = array(
        'main.jsp'                     =>  null,
        'metrics.jsp'                  =>  null,
        'xsl/buildresults.xsl'         =>  null,
    );
    
    /**
     * Executes this mode task.
     *
     * @return void
     */
    public function execute()
    {
        printf( '%sModifying CruiseControl files.%s', PHP_EOL, PHP_EOL );
        $this->copyModifiedFiles();
        
        printf( '%sInstalling new CruiseControl files.%s', PHP_EOL, PHP_EOL );
        $this->copyInstallFiles();
        
        printf( '%sModifying CruiseControl stylesheet.%s', PHP_EOL, PHP_EOL );
        $this->modifyStylesheet();
    }
    
    private function copyModifiedFiles()
    {
        $path = sprintf( 
            '%s/webapps/cruisecontrol/', 
            $this->getCCSetting()->ccInstallDir 
        );
        
        foreach ( $this->modifiedFiles as $filename => $content )
        {
            if ( file_exists( "{$path}/{$filename}.orig" ) === false )
            {
                printf(
                    '  Creating backup of "%s" as "%s.orig".%s',
                    $filename,
                    $filename,
                    PHP_EOL
                );
                copy( "{$path}/{$filename}", "{$path}/{$filename}.orig" );
            }
            
            if ( $content === null )
            {
                $content = $this->loadFileContent(
                    dirname( __FILE__ ) . '/../data/' . $filename
                );
                
                $this->modifiedFiles[$filename] = $content;
            }
            
            printf( '  File "%s" modified.%s', $filename, PHP_EOL );
            file_put_contents( "{$path}/{$filename}", base64_decode( $content ) );
        }
    }
    
    private function copyInstallFiles()
    {
        $path = sprintf( 
            '%s/webapps/cruisecontrol/', 
            $this->getCCSetting()->ccInstallDir 
        );
        
        foreach ( $this->installFiles as $filename => $content )
        {
            if ( $content === null )
            {
                $content = $this->loadFileContent(
                    dirname( __FILE__ ) . '/../data/' . $filename
                );
                
                $this->modifiedFiles[$filename] = $content;
            }
            
            printf( '  File "%s" installed.%s', $filename, PHP_EOL );
            file_put_contents( "{$path}/{$filename}", base64_decode( $content ) );
        }
    }
    
    private function modifyStylesheet()
    {
        $path = sprintf( 
            '%s/webapps/cruisecontrol/css/cruisecontrol.css', 
            $this->getCCSetting()->ccInstallDir 
        );
        
        if ( file_exists( "{$path}.orig" ) === false )
        {
            echo '  Creating backup of "css/cruisecontrol.css" as "css/cruisecontrol.css.orig".' . PHP_EOL;
            copy( $path, "{$path}.orig" );            
        }
        else
        {
            return;
        }
        
        $css = sprintf(
            '%s
.phpdoc-oddrow { background-color: #ccc; }
.phpdoc-error { font-family:arial,helvetica,sans-serif; font-size:8pt; color:#f00; }
.phpdoc-warning { font-family:arial,helvetica,sans-serif; font-size:8pt; color:#000; }
            ',
            file_get_contents( $path )
        );
        
        file_put_contents( $path, $css );
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