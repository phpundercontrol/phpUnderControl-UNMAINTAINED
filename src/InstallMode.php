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