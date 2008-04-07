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
 * @category   QualityAssurance
 * @package    Data
 * @subpackage Logs
 * @author     Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright  2007-2008 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpundercontrol.org/
 */

/**
 * Aggregates a set of PHPUnit coverage xml documents into a new coverage log.
 * 
 * @category   QualityAssurance
 * @package    Data
 * @subpackage Logs
 * @author     Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright  2007-2008 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpundercontrol.org/
 */
class phpucPHPUnitCoverageAggregator extends phpucAbstractLogAggregator
{
    /**
     * List of all file elements in the log file.
     *
     * @type array<DOMElement>
     * @var array(string=>DOMElement) $files
     */
    protected $files = array();
    
    /**
     * List of all lines in a log file element.
     *
     * @type array<DOMElement>
     * @var array(string=>DOMElement) $lines
     */
    protected $lines = array();
    
    /**
     * Stores the generated coverage log file.
     *
     * @param string $fileName The log file name.
     * 
     * @return void
     */
    public function save( $fileName )
    {
        $this->log->save( $fileName );
    }
    
    /**
     * Aggregates the results of all log files in the given iterator.
     *
     * @param Iterator $files List of coverage log files.
     * 
     * @return void
     */
    public function aggregate( Iterator $files )
    {
        foreach ( $files as $file )
        {
            $log = new DOMDocument();
            
            $log->preserveWhiteSpace = false;
            $log->formatOutput       = true;
            
            $log->load( $file );
            
            if ( $this->log === null )
            {
                $this->log   = $log;
                $this->files = $this->getFilesInLog( $log );
                
                foreach ( $this->files as $file => $elem )
                {
                    $this->lines[$file] = $this->getLinesInFile( $elem );
                }
            }
            else
            {
                $this->synchronizeFiles( $log );
            }
        }
        
        $this->updateFileMetrics();
        
        krsort( $this->files );
        
        $project = $this->log->getElementsByTagName( 'project' )->item( 0 );
        
        foreach ( $this->files as $file )
        {
            if ( $file->parentNode )
            {
                $file->parentNode->removeChild( $file );
            }
            $project->insertBefore( $file, $project->firstChild );
        }
    }
    
    /**
     * Synchronizes the files in the context log file and the given log file.
     * 
     * This method is useful if the test suite doesn't use a whitelist.
     *
     * @param DOMDocument $log The current log file to process.
     * 
     * @return void
     */
    protected function synchronizeFiles( DOMDocument $log )
    {
        foreach ( $this->getFilesInLog( $log ) as $file => $elem )
        {
            if ( isset( $this->files[$file] ) )
            {
                $this->synchronizeLines( $elem );
                continue;
            }
            
            // Import this element.
            $node = $this->log->importNode( $elem, true );
            
            // Store new file element
            $this->files[$file] = $node;
            $this->lines[$file] = $this->getLinesInFile( $node );
        }
    }
    
    /**
     * Synchronizes the line statistic for the given file element. 
     *
     * @param DOMElement $file The current context file.
     * 
     * @return void
     */
    protected function synchronizeLines( DOMElement $file )
    {
        // Get file line context
        $lines = $this->lines[$file->getAttribute( 'name' )];
        
        foreach ( $this->getLinesInFile( $file ) as $num => $line )
        {
            $count = (int) $line->getAttribute( 'count' );
            
            if ( $count > 0 )
            {
                $lines[$num]->setAttribute(
                    'count',
                    $count + (int) $lines[$num]->getAttribute( 'count' )
                );
            }
        }
    }
    
    /**
     * Updates all metrics elements in the context log file.
     *
     * @return void
     */
    protected function updateFileMetrics()
    {
        $projectClassCount        = 0;
        $projectMethodCount       = 0;
        $projectElementCount      = 0;
        $projectStatementCount    = 0;
        $projectCoveredMethods    = 0;
        $projectCoveredElements   = 0;
        $projectCoveredStatements = 0; 
        
        foreach ( $this->files as $file => $elem )
        {
            $classes = $elem->getElementsByTagName( 'class' );
            
            $fileClassCount        = $classes->length;
            $fileMethodCount       = 0;
            $fileElementCount      = count( $this->lines[$file] );
            $fileStatementCount    = 0;
            $fileCoveredMethods    = 0;
            $fileCoveredElements   = 0;
            $fileCoveredStatements = 0;
            
            foreach ( $classes as $class )
            {
                $class->parentNode->removeChild( $class );
            }
            
            foreach ( $this->lines[$file] as $line )
            {
                $type  = $line->getAttribute( 'type' );
                $count = (int) $line->getAttribute( 'count' );
                
                if ( $type === 'stmt' )
                {
                    ++$fileStatementCount;
                    if ( $count > 0 )
                    {
                        ++$fileCoveredStatements;
                        ++$fileCoveredElements;
                    }
                }
                if ( $type === 'method' )
                {
                    ++$fileMethodCount;
                    if ( $count > 0 )
                    {
                        ++$fileCoveredMethods;
                        ++$fileCoveredElements;
                    }
                }
            }
            
            $metric = $elem->getElementsByTagName( 'metrics' )->item( 0 );
            $metric->setAttribute( 'classes', $fileClassCount );
            $metric->setAttribute( 'methods', $fileMethodCount );
            $metric->setAttribute( 'elements', $fileElementCount );
            $metric->setAttribute( 'statements', $fileStatementCount );
            $metric->setAttribute( 'coveredmethods', $fileCoveredMethods );
            $metric->setAttribute( 'coveredelements', $fileCoveredElements );
            $metric->setAttribute( 'coveredstatements', $fileCoveredStatements );
            
            $metric->removeAttribute( 'executedlines' );
            $metric->removeAttribute( 'executablelines' );
            
            $projectClassCount        += $fileClassCount;
            $projectMethodCount       += $fileMethodCount;
            $projectElementCount      += $fileElementCount;
            $projectStatementCount    += $fileStatementCount;
            $projectCoveredMethods    += $fileCoveredMethods;
            $projectCoveredElements   += $fileCoveredElements;
            $projectCoveredStatements += $fileCoveredStatements;
        }
        
        $xpath  = new DOMXPath( $this->log );
        $metric = $xpath->query( '/coverage/project/metrics' )->item( 0 );

        $metric->setAttribute( 'classes', $projectClassCount );
        $metric->setAttribute( 'methods', $projectMethodCount );
        $metric->setAttribute( 'elements', $projectElementCount );
        $metric->setAttribute( 'statements', $projectStatementCount );
        $metric->setAttribute( 'coveredmethods', $projectCoveredMethods );
        $metric->setAttribute( 'coveredelements', $projectCoveredElements );
        $metric->setAttribute( 'coveredstatements', $projectCoveredStatements );
        
        $metric->removeAttribute( 'executedlines' );
        $metric->removeAttribute( 'executablelines' );
    }
    
    /**
     * Returns an array with all file elements in the given log file.
     *
     * @param DOMDocument $log The context log file.
     * 
     * @return array(string=>DOMElement)
     */
    protected function getFilesInLog( DOMDocument $log )
    {
        $files = array();
        foreach ( $log->getElementsByTagName( 'file' ) as $elem )
        {
            $files[$elem->getAttribute( 'name' )] = $elem; 
        }
        return $files;
    }
    
    /**
     * Returns an array with all line elements in the given file element.
     *
     * @param DOMElement $file The context file element.
     * 
     * @return array(string=>DOMElement)
     */
    protected function getLinesInFile( DOMElement $file )
    {
        $lines = array();
        foreach ( $file->getElementsByTagName( 'line' ) as $line )
        {
            $lines[$line->getAttribute( 'num' )] = $line;
        }
        return $lines;
    }
}