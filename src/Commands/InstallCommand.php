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
 * @subpackage Commands
 */

/**
 * Command implementation for the install mode.
 *
 * @package    phpUnderControl
 * @subpackage Commands
 * @author     Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright  2007 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    $Id: InstallMode.php 1699 2007-11-23 15:18:12Z mapi $
 */
class phpucInstallCommand extends phpucAbstractCommand
{
    /**
     * List of additional directories for phpUnderControl.
     *
     * @type array<string>
     * @var array(string)
     */
    private $directories = array(
        'images/php-under-control',
        'js'
    );
    
    /**
     * List of new files.
     *
     * @type array<string>
     * @var array(string=>string) $installFiles
     */
    private $installFiles = array(
        'header.jsp',
        'phpcs.jsp',
        'phpunit.jsp',
        'phpunit-pmd.jsp',
        'css/php-under-control.css',
        'css/SyntaxHighlighter.css',
        'images/php-under-control/error.png',
        'images/php-under-control/failed.png',
        'images/php-under-control/header-center.png',
        'images/php-under-control/header-left-logo.png',
        'images/php-under-control/info.png',
        'images/php-under-control/skipped.png',
        'images/php-under-control/success.png',
        'images/php-under-control/tab-active.png',
        'images/php-under-control/tab-inactive.png',
        'images/php-under-control/warning.png',
        'js/shBrushPhp.js',
        'js/shCore.js',
        'xsl/phpcs.xsl',
        'xsl/phpcs-details.xsl',
        'xsl/phpdoc.xsl',
        'xsl/phphelper.xsl',
        'xsl/phpunit.xsl',
        'xsl/phpunit-details.xsl',
        'xsl/phpunit-pmd.xsl',
        'xsl/phpunit-pmd-details.xsl',
    );
    
    /**
     * List of modified files.
     *
     * @type array<string>
     * @var array(string=>string) $modifiedFiles
     */
    private $modifiedFiles = array(
        'index.jsp',
        'main.jsp',
        'metrics.jsp',
        'xsl/buildresults.xsl',
        'xsl/errors.xsl',
        'xsl/header.xsl',
        'xsl/modifications.xsl',
    );
    
    /**
     * Executes this mode task.
     *
     * @return void
     */
    public function execute()
    {
        echo PHP_EOL . 'Creating required CruiseControl directories.' . PHP_EOL;
        $this->createDirectories();
        
        echo PHP_EOL . 'Modifying CruiseControl files.' . PHP_EOL;
        $this->copyModifiedFiles();
        
        echo PHP_EOL . 'Installing new CruiseControl files.' . PHP_EOL;
        $this->copyInstallFiles();
    }
    
    /**
     * Creates required directories phpUnderControl.
     *
     * @return void
     */
    private function createDirectories()
    {
        // Get root directory.
        $installDir = $this->consoleArgs->getArgument( 'cc-install-dir' );
        
        foreach ( $this->directories as $index => $directory )
        {
            $path = sprintf( 
                '%s/webapps/cruisecontrol/%s', 
                $installDir, 
                $directory
            );
            
            // Skip for existing directories.
            if ( is_dir( $path ) === true )
            {
                continue;
            }
            
            printf( 
                ' % 2d. Creating directory "webapps/cruisecontrol/%s".%s',
                $index,
                $directory,
                PHP_EOL
            );
            mkdir( $path );
        }
    }
    
    private function copyModifiedFiles()
    {
        $path = sprintf( 
            '%s/webapps/cruisecontrol/', 
            $this->consoleArgs->getArgument( 'cc-install-dir' ) 
        );
        
        foreach ( $this->modifiedFiles as $filename )
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
            
            printf( '  File "%s" modified.%s', $filename, PHP_EOL );
            
            file_put_contents( 
                "{$path}/{$filename}",
                $this->loadFileContent(
                    sprintf( '%s/data/%s', self::$baseDir, $filename )
                )
            );
        }
    }
    
    private function copyInstallFiles()
    {
        $path = sprintf( 
            '%s/webapps/cruisecontrol/', 
            $this->consoleArgs->getArgument( 'cc-install-dir' )
        );
        
        foreach ( $this->installFiles as $filename )
        {
            
            printf( '  File "%s" installed.%s', $filename, PHP_EOL );
            
            file_put_contents( 
                "{$path}/{$filename}",
                $this->loadFileContent(
                    sprintf( '%s/data/%s', self::$baseDir, $filename )
                ) 
            );
        }
    }
    
    /**
     * Loads the file content.
     *
     * @param string $file The file name.
     * 
     * @return string 
     */
    private function loadFileContent( $file )
    {
        return file_get_contents( $file );
    }
}