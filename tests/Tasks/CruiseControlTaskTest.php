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
 * @package   Tasks
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

require_once dirname( __FILE__ ) . '/AbstractTaskTest.php';

/**
 * Test case for the cruise control task.
 *
 * @package   Tasks
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucCruiseControlTaskTest extends phpucAbstractTaskTest
{
    /**
     * The console arguments.
     *
     * @type phpucConsoleArgs
     * @var phpucConsoleArgs $args
     */
    protected $args = null;
    
    /**
     * Prepares the required <b>$argv</b> array.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->prepareArgv( array( 'install', PHPUC_TEST_DIR ) );
        
        $input = new phpucConsoleInput();
        $input->parse();
        
        $this->args = $input->args;
    }
    
    /**
     * Tests that the cc task fails for an invalid root directory.
     *
     * @return void
     */
    public function testValidateWithInvalidCCInstallDirFail()
    {
        $this->prepareArgv( array( 'install', PHPUC_TEST_DIR . '/foobar' ) );
        
        $input = new phpucConsoleInput();
        $input->parse();
        
        $ccTask = new phpucCruiseControlTask();
        $ccTask->setConsoleArgs( $input->args );
        
        $this->setExpectedException( 'phpucValidateException' );
        $ccTask->validate();        
    }
    
    /**
     * Tests that the validate method fails for an invalid directory structure.
     *
     * @return void
     */
    public function testValidateWithInValidDirectoryStructureFail()
    {
        $this->createTestDirectories(
            array( 
                'webapps', 
                'webapps/cruisecontrol',
                'webapps/cruisecontrol/xsl',
                'webapps/cruisecontrol/images',
            )
        );
        
        $ccTask = new phpucCruiseControlTask();
        $ccTask->setConsoleArgs( $this->args );
        
        $this->setExpectedException( 'phpucValidateException' );
        $ccTask->validate();
    }
    
    /**
     * Tests the validate method with a valid directory structure and expects
     * that the cc task works as expected. 
     *
     * @return void
     */
    public function testValidateWithValidDirectoryStructure()
    {
        $this->createTestDirectories(
            array( 
                'webapps', 
                'webapps/cruisecontrol',
                'webapps/cruisecontrol/css',
                'webapps/cruisecontrol/xsl',
                'webapps/cruisecontrol/images',
            )
        );
        
        $ccTask = new phpucCruiseControlTask();
        $ccTask->setConsoleArgs( $this->args );
        $ccTask->validate();
    }
    
    /**
     * Tests that the cc task creates the expected directories.
     *
     * @return void
     */
    public function testExecuteWithValidDirectoryStructure()
    {
        $this->createTestDirectories(
            array( 
                'webapps', 
                'webapps/cruisecontrol',
                'webapps/cruisecontrol/css',
                'webapps/cruisecontrol/xsl',
                'webapps/cruisecontrol/images',
            )
        );
        
        $ccTask = new phpucCruiseControlTask();
        $ccTask->setConsoleArgs( $this->args );
        $ccTask->execute();
        
        $basedir =  PHPUC_TEST_DIR . '/webapps/cruisecontrol';
        
        $this->assertFileExists( $basedir . '/js' );
        $this->assertFileExists( $basedir . '/images/php-under-control' );
    }
}