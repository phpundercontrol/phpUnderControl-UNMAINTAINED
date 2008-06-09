<?php
/**
 * This file is part of phpUnderControl.
 * 
 * PHP Version 5.2.0
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
 * @package   SourceBrowser
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

/**
 * ...
 *
 * @category  QualityAssurance
 * @package   SourceBrowser
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucSourceBrowserGenerator
{
    private $sourceDir = null;
    
    private $logDir = null;
    
    private $logFile = null;
    
    /**
     * Constructs a new html generator.
     *
     * @param string $sourceDir Directory with the PHPUnit --coverage-source files.
     * @param string $logDir    Directory with all xml log files.
     * 
     * @throws phpucErrorException 
     *         If the given log or source directory doesn't exist.
     */
    public function __construct( $sourceDir, $logDir )
    {
        if ( is_dir( $sourceDir ) === false )
        {
            throw new phpucErrorException( "Invalid src directory '{$sourceDir}'." );
        }
        if ( is_dir( $logDir ) === false )
        {
            throw new phpucErrorException( "Invalid log directory '{$logDir}'." );
        }
        
        $this->sourceDir = $sourceDir;
        $this->logDir    = $logDir;
        
        // Temporary merged log file
        $this->logFile = phpucFileUtil::getSysTempDir() . '/phpundercontrol.xml';
    }
    
    /**
     * The dtor removes the temporary log file.
     */
    public function __destruct()
    {
        if ( file_exists( $this->logFile ) )
        {
            unlink( $this->logFile );
        }
    }
    
    /**
     * Generates the html output.
     *
     * @param string $targetDir The output target.
     * 
     * @return void
     */
    public function generate( $targetDir )
    {
        if ( !file_exists( $targetDir ) )
        {
            mkdir( $targetDir, 0755, true );
        }
        else if ( is_dir( $targetDir ) === false )
        {
            throw new phpucErrorException(
                "Output directory '{$targetDir}' exists and is no directory."
            );
        }
        
        $log   = $this->generateMergedLogFile();
        $files = $this->extractCoveredFiles( $log );

        foreach ( glob( "{$this->sourceDir}/*.xml" ) as $file )
        {
            $source = new DOMDocument( '1.0', 'UTF-8' );
            $source->load( $file );
            
            $fileName = $source->documentElement->getAttribute( 'fullPath' );
            $fileName = $this->normalizeFileName( $fileName );
            
            if ( in_array( $fileName, $files ) )
            {
                
            }
        }
    }
    
    /**
     * Generates the merged log file.
     *
     * @return DOMDocument
     * @throws phpucErrorException 
     *         If the given log directory doesn't exist.
     */
    private function generateMergedLogFile()
    {
        $merger = new phpucLogMerger( $this->logDir );
        return $merger->mergeFiles( $this->logFile );
    }
    
    private function extractCoveredFiles( DOMDocument $log )
    {
        $files = array();
        
        $xpath  = new DOMXPath( $log );
        $result = $xpath->query( '/phpundercontrol/coverage/project/file' );
        
        foreach ( $result as $file )
        {
            $files[] = $this->normalizeFileName( $file->getAttribute( 'name' ) );
        }
        return $files;
    }
    
    private function normalizeFileName( $fileName )
    {
        // Replace windows backslash
        $fileName = str_replace( '\\', '/', $fileName );
        // Replace all double slashes
        return preg_replace( '#/+#', '/', $fileName );
    }
}