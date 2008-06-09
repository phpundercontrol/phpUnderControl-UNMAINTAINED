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

require_once dirname( __FILE__ ) . '/../AbstractTest.php';

/**
 * Test case for the log file merger.
 *
 * @category  QualityAssurance
 * @package   SourceBrowser
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucLogMergerTest extends phpucAbstractTest
{
    /**
     * Tests that the ctor throws an exception for an invalid log directory.
     *
     * @return void
     */
    public function testCtorThrowsExceptionForInvalidLogDir()
    {
        $logDir = PHPUC_TEST_DIR . '/logs';
        
        $this->assertFileNotExists( $logDir );
        $this->setExpectedException(
            'phpucErrorException',
            "Invalid log directory '{$logDir}'."
        );
        
        new phpucLogMerger( $logDir );
    }
    
    /**
     * Tests the {@link phpucLogMerger::mergeFiles()} method and output.
     *
     * @return void
     */
    public function testMergeFiles()
    {
        $input  = PHPUC_TEST_DATA . '/coverage/logs';
        $output = PHPUC_TEST_DIR . '/log.xml';
        
        $this->assertFileNotExists( $output );
        
        $merger = new phpucLogMerger( $input );
        $result = $merger->mergeFiles( $output );
        
        $this->assertType( 'DOMDocument', $result );
        $this->assertFileExists( $output );
        
        $this->assertEquals( 'phpundercontrol', $result->documentElement->tagName );
        
        $expected = array(
            'checkstyle'  =>  true,
            'coverage'    =>  true,
            'metrics'     =>  true,
            'testsuites'  =>  true
        );
        
        foreach ( $result->documentElement->childNodes as $childNode )
        {
            if ( $childNode->nodeType !== XML_ELEMENT_NODE )
            {
                continue;
            }
            
            $this->assertArrayHasKey( $childNode->tagName, $expected );
            unset( $expected[$childNode->tagName] );
        }
        $this->assertEquals( 0, count( $expected ) );
    }
}