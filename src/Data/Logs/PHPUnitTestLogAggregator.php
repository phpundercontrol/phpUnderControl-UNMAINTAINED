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
 * Aggregates multiple PHPUnit test log files. 
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
class phpucPHPUnitTestLogAggregator extends phpucAbstractLogAggregator
{
    protected $currentBuild = null;
    
    protected $testSuites = array();
    
    protected $mergeSuites = array();
    
    /**
     * Aggregates the results of all log files in the given iterator.
     *
     * @param Iterator $files List of coverage log files.
     * 
     * @return void
     */
    public function aggregate( Iterator $files )
    {
        // Create a new empty log
        $this->log = $this->createLog();
        
        // List of all broken builds
        $brokenBuilds = array();
        
        foreach ( $files as $build => $file )
        {
            $log = new DOMDocument( '1.0', 'UTF-8' );
            
            $log->preserveWhiteSpace = false;
            $log->formatOutput       = true;
            
            // Load log file and validate
            if ( !$log->load( $file ) || !$this->isValidTestLog( $log ) )
            {
                // Store broken build identifier.
                $brokenBuilds[] = $build;
                
                // Skip broken logs
                continue;
            }
            
            $this->currentBuild = $build;
            
            $this->traverseTestSuites( $log->documentElement, $this->log->documentElement );
        }
    }
    
    protected function traverseTestSuites( DOMElement $source, DOMElement $target )
    {
        foreach ( $source->childNodes as $node )
        {
            if ( $node->nodeType !== XML_ELEMENT_NODE )
            {
                continue;
            }
            else if ( $node->nodeName === 'testsuite' )
            {
                $name = $node->getAttribute( 'name' );

                if ( !isset( $this->testSuites[$name] ) )
                {
                    $suite = $this->log->createElement( 'testsuite' );
                    foreach ( $node->attributes as $attribute )
                    {
                        $suite->setAttribute( $attribute->nodeName, $attribute->nodeValue );
                    }
                    $target->appendChild( $suite );
                    
                    $this->testSuites[$name] = $suite;
                }
                else
                {
                    $suite = $this->testSuites[$name];
                    
                    $suite->setAttribute(
                        'tests',
                        (integer) $suite->getAttribute( 'tests' ) +
                        (integer) $node->getAttribute( 'tests' )
                    );
                    $suite->setAttribute(
                        'failures',
                        (integer) $suite->getAttribute( 'failures' ) +
                        (integer) $node->getAttribute( 'failures' )
                    );
                    $suite->setAttribute(
                        'errors',
                        (integer) $suite->getAttribute( 'errors' ) +
                        (integer) $node->getAttribute( 'errors' )
                    );
                    $suite->setAttribute(
                        'time',
                        (float) $suite->getAttribute( 'time' ) +
                        (float) $node->getAttribute( 'time' )
                    );
                }
                
                if ( strpos( $name, '::' ) === false )
                {
                    $this->traverseTestSuites( $node, $suite );
                }
                else
                {
                    $new = $this->log->importNode( $node, true );
                    $new->setAttribute( 'build', $this->currentBuild );
                    
                    $suite->appendChild( $new );
                }
            }
            else if ( $node->nodeName === 'testcase' )
            {
                $name = sprintf(
                    '%s::%s',
                    $node->getAttribute( 'class' ),
                    $node->getAttribute( 'name' )
                );
                
                if ( !isset( $this->mergeSuites[$name] ) )
                {
                    $suite = $this->log->createElement( 'testsuite' );
                    $suite->setAttribute( 'name', $name );
                    $suite->setAttribute( 'time', '0.0' );
                    $suite->setAttribute( 'tests', '0' );
                    $suite->setAttribute( 'errors', '0' );
                    $suite->setAttribute( 'failures', '0' );
                    
                    $this->mergeSuites[$name] = $suite;
                    
                    $target->appendChild( $suite );
                }
                else
                {
                    $suite = $this->mergeSuites[$name];
                }
                
                $suite->setAttribute(
                    'tests', 1 + (integer) $suite->getAttribute( 'tests' )
                );
                $suite->setAttribute(
                    'errors',
                    (integer) $suite->getAttribute( 'errors' ) +
                    $node->getElementsByTagName( 'error' )->length
                );
                $suite->setAttribute(
                    'failures',
                    (integer) $suite->getAttribute( 'failures' ) +
                    $node->getElementsByTagName( 'failure' )->length
                );
                $suite->setAttribute(
                    'time',
                    (float) $suite->getAttribute( 'time' ) +
                    (float) $node->getAttribute( 'time' )
                );
                
                $test = $this->log->importNode( $node, true );
                $test->setAttribute( 'build', $this->currentBuild );
                
                $suite->appendChild( $test );
            }
        }
    }
    
    protected function isValidTestLog( DOMDocument $log )
    {
        if ( $log->documentElement === null )
        {
            return false;
        }
        return ( $log->documentElement->nodeName === 'testsuites' );
    }
    
    protected function createLog()
    {
        $log               = new DOMDocument( '1.0', 'UTF-8' );
        $log->formatOutput = true;
        
        $log->appendChild( $log->createElement( 'testsuites' ) );
        
        return $log;
    }
}