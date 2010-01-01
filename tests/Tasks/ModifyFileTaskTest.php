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
 * @category  QualityAssurance
 * @package   Tasks
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

require_once dirname( __FILE__ ) . '/AbstractTaskTest.php';

/**
 * Test case for the file modify task.
 * 
 * @category  QualityAssurance
 * @package   Tasks
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucModifyFileTaskTest extends phpucAbstractTaskTest
{
    /**
     * Tests that the validate method works for existing files.
     *
     * @return void
     */
    public function testValidate()
    {
        // Create test directory
        $this->createTestDirectories( array( 'test' ) );
        
        $this->assertFileNotExists( PHPUC_TEST_DIR . '/test/test1.jsp' );
        $this->assertFileNotExists( PHPUC_TEST_DIR . '/test/test2.jsp' );
        
        // Create test files.
        $this->createTestFile( 'test/test1.jsp' );
        $this->createTestFile( 'test/test2.jsp' );
        
        $this->assertFileExists( PHPUC_TEST_DIR . '/test/test1.jsp' );
        $this->assertFileExists( PHPUC_TEST_DIR . '/test/test2.jsp' );
        
        // Prepare args
        $this->prepareArgv( array( 'install', PHPUC_TEST_DIR ) );
        
        $input = new phpucConsoleInput();
        $input->parse();
        
        $task = new phpucModifyFileTask();
        $task->setConsoleArgs( $input->args );
        $task->setFiles( array( '/test/test1.jsp', '/test/test2.jsp' ) );
        $task->validate();
    }
    
    /**
     * Tests that the {@link phpucModifyFileTask::validate()} method fails for
     * a not existing file with a {@link phpucValidateException}.
     *
     * @return void
     */
    public function testValidateWithNotExistingFileFail()
    {
        $this->setExpectedException( 'phpucValidateException' );
        
        // Create test directory
        $this->createTestDirectories( array( 'test' ) );
        
        $this->assertFileNotExists( PHPUC_TEST_DIR . '/test/test1.jsp' );
        $this->assertFileNotExists( PHPUC_TEST_DIR . '/test/test2.jsp' );
        
        // Create test files.
        $this->createTestFile( 'test/test1.jsp' );
        
        $this->assertFileExists( PHPUC_TEST_DIR . '/test/test1.jsp' );
        
        // Prepare args
        $this->prepareArgv( array( 'install', PHPUC_TEST_DIR ) );
        
        $input = new phpucConsoleInput();
        $input->parse();
        
        $task = new phpucModifyFileTask();
        $task->setConsoleArgs( $input->args );
        $task->setFiles( array( '/test/test1.jsp', '/test/test2.jsp' ) );
        $task->validate();
    }
    
    /**
     * Tests the main execute method.
     *
     * @return void
     */
    public function testExecute()
    {
        // Create test directory
        $this->createTestDirectories( array( 'webapps/cruisecontrol' ) );
        
        $this->assertFileNotExists( PHPUC_TEST_DIR . '/webapps/cruisecontrol/index.jsp' );
        $this->assertFileNotExists( PHPUC_TEST_DIR . '/webapps/cruisecontrol/metrics.cewolf.jsp' );
        
        // Create test files.
        $this->createTestFile( 'webapps/cruisecontrol/index.jsp', '' );
        $this->createTestFile( 'webapps/cruisecontrol/metrics.cewolf.jsp', '' );
        
        $this->assertFileExists( PHPUC_TEST_DIR . '/webapps/cruisecontrol/index.jsp' );
        $this->assertFileExists( PHPUC_TEST_DIR . '/webapps/cruisecontrol/metrics.cewolf.jsp' );
        
        // Prepare args
        $this->prepareArgv( array( 'install', PHPUC_TEST_DIR ) );
        
        $input = new phpucConsoleInput();
        $input->parse();
        
        $task = new phpucModifyFileTask();
        $task->setConsoleArgs( $input->args );
        $task->setFiles(
            array( 
                '/webapps/cruisecontrol/index.jsp', 
                '/webapps/cruisecontrol/metrics.cewolf.jsp' 
            )
        );
        $task->execute();
        
        $this->assertFileExists( PHPUC_TEST_DIR . '/webapps/cruisecontrol/index.jsp.orig' );
        $this->assertFileExists( PHPUC_TEST_DIR . '/webapps/cruisecontrol/metrics.cewolf.jsp.orig' );
        
        $this->assertEquals(
            md5_file( PHPUC_DATA_DIR . '/webapps/cruisecontrol/index.jsp' ),
            md5_file( PHPUC_TEST_DIR . '/webapps/cruisecontrol/index.jsp' )
        );
        $this->assertEquals(
            md5_file( PHPUC_DATA_DIR . '/webapps/cruisecontrol/metrics.cewolf.jsp' ),
            md5_file( PHPUC_TEST_DIR . '/webapps/cruisecontrol/metrics.cewolf.jsp' )
        );
    }
    
    /**
     * Tests the execute method against a java server page that has placeholders
     * which were replaced in this test.
     *
     * @return void
     */
    public function testExecuteWithCustomizedJavaServerPage()
    {
        // Create test directory
        $this->createTestDirectories( array( 'webapps/cruisecontrol' ) );
        
        $this->assertFileNotExists( PHPUC_TEST_DIR . '/webapps/cruisecontrol/main.jsp' );
        
        $custom1 = '<%-- begin phpUnderControl 3 --%>
            Hello
            <%-- end phpUnderControl 3 --%>';
        $custom2 = '<%-- begin phpUnderControl 5 --%>
            World
            <%-- end phpUnderControl 5 --%>';
        $custom3 = '<%-- begin phpUnderControl 6 --%>
            Baby
            <%-- end phpUnderControl 6 --%>';
        
        // Create test files.
        $this->createTestFile( 'webapps/cruisecontrol/main.jsp', "
            {$custom1}
            {$custom2}
            {$custom3}
        " );
        
        $this->assertFileExists( PHPUC_TEST_DIR . '/webapps/cruisecontrol/main.jsp' );
        
        // Prepare args
        $this->prepareArgv( array( 'install', PHPUC_TEST_DIR ) );
        
        $input = new phpucConsoleInput();
        $input->parse();
        
        $task = new phpucModifyFileTask();
        $task->setConsoleArgs( $input->args );
        $task->setFiles( array( '/webapps/cruisecontrol/main.jsp' ) );
        $task->execute();
        
        $content = file_get_contents( PHPUC_TEST_DIR . '/webapps/cruisecontrol/main.jsp' );
        
        $this->assertContains( $custom1, $content );
        $this->assertContains( $custom2, $content );
        $this->assertContains( $custom3, $content );
    }
}