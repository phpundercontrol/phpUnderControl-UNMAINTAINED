<?php
/**
 * This file is part of phpUnderControl.
 *
 * Copyright (c) 2007-2010, Manuel Pichler <mapi@phpundercontrol.org>.
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
 * @package   Data
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

require_once dirname( __FILE__ ) . '/AbstractConfigTest.php';

/**
 * Test cases for the cc config file.
 *
 * @package   Data
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucConfigFileTest extends phpucAbstractConfigTest
{
    /**
     * Tests the {@link phpucConfigFile} ctor.
     *
     * @return void
     */
    public function testNewConfigFileInstance()
    {
        $this->createTestFile( '/config.xml', $this->testXml );
        
        $config = new phpucConfigFile( $this->testFile );
        $config->store();
        
        $this->assertXmlStringEqualsXmlString(
            $this->testXml,
            file_get_contents( $this->testFile )
        );
    }
    
    /**
     * Tests the {@link phpucConfigFile} ctor with a not existing file.
     *
     * @return void
     */
    public function testNewConfigFileInstanceFail()
    {
        $this->setExpectedException( 'phpucErrorException' );
        
        new phpucConfigFile( $this->testFile );
    }
    
    /**
     * Tests the {@link phpucConfigFile::createProject()} and the 
     * {@link phpucConfigFile::getProject()} methods. 
     *
     * @return void
     */
    public function testCreateProject()
    {
        $this->createTestFile( '/config.xml', $this->testXml );
        
        $config  = new phpucConfigFile( $this->testFile );
        $project = $config->createProject( 'phpUnderControl' );
        
        $this->assertType( 'phpucConfigProject', $project );
        
        $config->store();
        
        $this->assertXmlStringNotEqualsXmlString(
            $this->testXml,
            file_get_contents( $this->testFile )
        );
        
        // Now try to reload the config and read the new project
        $config  = new phpucConfigFile( $this->testFile );
        $project = $config->getProject( 'phpUnderControl' );
        
        $this->assertType( 'phpucConfigProject', $project );
    }
    
    /**
     * This method tests that the {@link phpucConfigFile::getProject()} method
     * fails with an expection, if no project for the given name exists.
     *
     * @return void
     */
    public function testGetProjectFail()
    {
        $this->setExpectedException('phpucErrorException');
        
        $this->createTestFile( '/config.xml', $this->testXml );
        
        $config = new phpucConfigFile( $this->testFile );
        $config->getProject( 'phpUnderControl' );
    }
}