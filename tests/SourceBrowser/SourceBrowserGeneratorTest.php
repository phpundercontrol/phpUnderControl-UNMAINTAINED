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
class phpucSourceBrowserGeneratorTest extends phpucAbstractTest
{
    /**
     * Tests that the ctor throws an exception for an invalid source directory. 
     *
     * @return void
     */
    public function testCtorThrowsExceptionForInvalidSourceDirectory()
    {
        $source = PHPUC_TEST_DIR . '/source';
        $logs   = PHPUC_TEST_DATA . '/coverage/logs';
        
        $this->assertFileNotExists( $source );
        $this->assertFileExists( $logs );
        
        $this->setExpectedException(
            'phpucErrorException',
            "Invalid src directory '{$source}'."
        );
        
        new phpucSourceBrowserGenerator( $source, $logs );
    }

    /**
     * Tests that the ctor throws an exception for an invalid log directory. 
     *
     * @return void
     */
    public function testCtorThrowsExceptionForInvalidLogDirectory()
    {
        $source = PHPUC_TEST_DATA . '/coverage/source';
        $logs   = PHPUC_TEST_DIR . '/logs';
        
        $this->assertFileExists( $source );
        $this->assertFileNotExists( $logs );
        
        $this->setExpectedException(
            'phpucErrorException',
            "Invalid log directory '{$logs}'."
        );
        
        new phpucSourceBrowserGenerator( $source, $logs );
    }
    
    /**
     * Tests that {@link phpucSourceBrowserGenerator::generate()} throws an
     * exception for an invalid target directory.
     *
     * @return void
     */
    public function testGenerateThrowsExceptionForExistingOutputDirAsFile()
    {
        $output = PHPUC_TEST_DIR . '/output';
        $source = PHPUC_TEST_DATA . '/coverage/source';
        $logs   = PHPUC_TEST_DATA . '/coverage/logs';
        
        $generator = new phpucSourceBrowserGenerator( $source, $logs );
        
        file_put_contents( $output, '...' );
        
        $this->setExpectedException(
            'phpucErrorException',
            "Output directory '{$output}' exists and is no directory."
        );
        
        $generator->generate( $output );
    }
    
    /**
     * Tests that {@link phpucSourceBrowserGenerator::generate()} creates the
     * required output directory.
     *
     * @return void
     */
    public function testGenerateCreatesRequiredTargetDirectory()
    {
        $output = PHPUC_TEST_DIR . '/php/under/control';
        $source = PHPUC_TEST_DATA . '/coverage/source';
        $logs   = PHPUC_TEST_DATA . '/coverage/logs';
        
        $this->assertFileNotExists( $output );
        
        $generator = new phpucSourceBrowserGenerator( $source, $logs );
        $generator->generate( $output );
        
        $this->assertFileExists( PHPUC_TEST_DIR . '/php/under/control' );
    }
    
    /**
     * Tests that the generate process creates the temporary log file.
     *
     * @return void
     */
    public function testGenerateProducesTheMergedLogFile()
    {
        $output = PHPUC_TEST_DIR . '/out';
        $source = PHPUC_TEST_DATA . '/coverage/source';
        $logs   = PHPUC_TEST_DATA . '/coverage/logs';
        
        $this->assertFileNotExists( $output );
        
        $generator = new phpucSourceBrowserGenerator( $source, $logs );
        $generator->generate( $output );
        
        // This is insider knowledge :)
        $logFile = phpucFileUtil::getSysTempDir() . '/phpundercontrol.xml';
        
        $this->assertFileExists( $logFile );
    }
}