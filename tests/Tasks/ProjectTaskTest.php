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

require_once dirname( __FILE__ ) . '/../AbstractTest.php';

/**
 * Test case for the project task.
 *
 * @package   Tasks
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucProjectTaskTest extends phpucAbstractTest
{
    /**
     * A prepared console arg object.
     * 
     * @type phpucConsoleArgs
     * @var phpucConsoleArgs $args
     */
    protected $args = null;
    
    /**
     * Creates a prepared {@link phpucConsoleArgs} instance and the required
     * /projects directory.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        chdir( dirname( __FILE__ ) );
        
        $this->prepareArgv( array(
            'example',
            '--project-name',
            'phpUnderControl',
            PHPUC_TEST_DIR
        ) );
        
        $input = new phpucConsoleInput();
        $input->parse();
        
        $this->args = $input->args;
        
        $this->createTestDirectories(
            array(
                '/projects',
                '/apache-ant-1.7.0'
            )
        );
        
        $this->createTestFile( '/config.xml', '<cruisecontrol />' );
    }
    
    /**
     * This test should run without any error.
     *
     * @return void
     */
    public function testValidateProjectTaskNoError()
    {
        $task = new phpucProjectTask();
        $task->setConsoleArgs( $this->args );
        $task->validate();
    }
    
    /**
     * Tests that the {@link phpucProjectTask::validate()} method fails with an
     * exception if no /projects directory exists.
     *
     * @return void
     */
    public function testValidateProjectTaskWithoutCCProjectsDirFail()
    {
        rmdir( PHPUC_TEST_DIR . '/projects' );
        
        $task = new phpucProjectTask();
        $task->setConsoleArgs( $this->args );
        
        $this->setExpectedException( 'phpucValidateException' );
        
        $task->validate();
    }
    
    /**
     * Tests that the {@link phpucProjectTask::validate()} method fails with an
     * exception if a project with the same name exists.
     *
     * @return void
     */
    public function testValidateProjectTaskWithExistingProjectDirectoryFail()
    {
        $this->createTestDirectories( array( '/projects/phpUnderControl' ) );
        
        $task = new phpucProjectTask();
        $task->setConsoleArgs( $this->args );
        
        $this->setExpectedException( 'phpucValidateException' );
        
        $task->validate();
    }
    
    /**
     * Tests the {@link phpucProjectTask::execute()} method which should not fail
     * and which should create some files and directories.
     *
     * @return void
     */
    public function testExecuteProjectTaskNoError()
    {
        $task = new phpucProjectTask();
        $task->setConsoleArgs( $this->args );
        $task->execute();
        
        $this->assertFileExists( PHPUC_TEST_DIR . '/projects/phpUnderControl' );
        $this->assertFileExists( PHPUC_TEST_DIR . '/projects/phpUnderControl/source' );
        $this->assertFileExists( PHPUC_TEST_DIR . '/projects/phpUnderControl/build' );
        $this->assertFileExists( PHPUC_TEST_DIR . '/projects/phpUnderControl/build/logs' );
        $this->assertFileExists( PHPUC_TEST_DIR . '/projects/phpUnderControl/build.xml' );
        
        $sxml = simplexml_load_file( PHPUC_TEST_DIR . '/config.xml' );
        $this->assertEquals( 1, count( $sxml->xpath( '//project[@name="phpUnderControl"]' ) ) );
        
        $sxml = simplexml_load_file( PHPUC_TEST_DIR . '/projects/phpUnderControl/build.xml' );
        $this->assertEquals( 'phpUnderControl', (string) $sxml['name'] );
    }
    
    public function testGetAntDirReturnsAntLocation()
    {
        mkdir( PHPUC_TEST_DIR . '/bin/ant', 0777, true );
        $task = new phpucProjectTask();
        $this->assertEquals(PHPUC_TEST_DIR . '/apache-ant-1.7.0', $task->getAntHome(PHPUC_TEST_DIR));
        
        rmdir( PHPUC_TEST_DIR . '/apache-ant-1.7.0' );
        $this->assertEquals(PHPUC_TEST_DIR, $task->getAntHome(PHPUC_TEST_DIR));
    }
    
    public function testGetAntDirReturnsExternalAntHome()
    {
        $ant = shell_exec( 'which ant' );
        rmdir( PHPUC_TEST_DIR . '/apache-ant-1.7.0' );

        $task = new phpucProjectTask();
        $anthome = $task->getAntHome(PHPUC_TEST_DIR);
        
        $this->assertThat(strstr(trim($ant), $anthome), $this->fileExists());
    }
    
    /**
     * Tests that the {@link phpucProjectTask::execute()} method fails with an
     * exception if no ant directory exists.
     *
     * @return void
     * @expectedException phpucExecuteException
     */
    public function testExecuteProjectTaskWithoutAntDirectoryFail()
    {
        rmdir( PHPUC_TEST_DIR . '/apache-ant-1.7.0' );
        
        $task = $this->getMock('phpucProjectTask', array('getAntHome'));
        $task->expects($this->once())
             ->method('getAntHome')
             ->will($this->returnValue(false));
                
        $task->setConsoleArgs( $this->args );        
        $task->execute();
    }
    
}
