<?php
/**
 * This file is part of phpUnderControl.
 * 
 * PHP Version 5.2.0
 *
 * Copyright (c) 2007-2010, Manuel Pichler <mapi@manuel-pichler.de>.
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
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

require_once dirname( __FILE__ ) . '/AbstractTaskTest.php';

/**
 * Test case for the {@link phpucProjectDeleteTask} class.
 *
 * @category  QualityAssurance
 * @package   Tasks
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucProjectDeleteTaskTest extends phpucAbstractTaskTest
{
    /**
     * The used console args instance.
     *
     * @type phpucConsoleArgs
     * @var phpucConsoleArgs $args
     */
    protected $args = null;
    
    /**
     * Creates the used console args instance.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->args = $this->prepareConsoleArgs(
            array(
                'delete',
                '--project-name',
                $this->projectName,
                PHPUC_TEST_DIR
            )
        );
    }
    
    /**
     * Tests that the {@link phpucProjectDeleteTask#validate()} method works for
     * an existing directory.
     * 
     * @return void
     */
    public function testValidateProjectDeleteTask()
    {
        $this->createCCConfig();
        $this->createTestDirectories( array( "/projects/{$this->projectName}" ) );
        
        $task = new phpucProjectDeleteTask();
        $task->setConsoleArgs( $this->args );
        $task->validate();
    }
    
    /**
     * Tests that {@link phpucProjectDeleteTask#validate()} fails with an
     * exception if the specified project doesn't exist.
     *
     * @return void
     */
    public function testValidateProjectDeleteTaskWithInvalidProjectFail()
    {
        $this->setExpectedException(
            'phpucValidateException',
            sprintf(
                "Missing project directory '%s/projects/%s'.",
                PHPUC_TEST_DIR,
                $this->projectName
            )
        );
        
        $this->createTestDirectories( array( "/projects/{$this->projectName}_foo" ) );
        
        $task = new phpucProjectDeleteTask();
        $task->setConsoleArgs( $this->args );
        $task->validate();
    }
    
    /**
     * Tests that {@link phpucProjectDeleteTask#validate()} fails with an
     * exception if the config.xml file doesn't exist.
     *
     * @return void
     */
    public function testValidateProjectDeleteTaskWithoutConfigXmlFail()
    {
        $this->setExpectedException(
            'phpucValidateException',
            sprintf(
                "Missing CruiseControl configuration '%s/config.xml'.",
                PHPUC_TEST_DIR
            )
        );
        
        $this->createTestDirectories( array( "/projects/{$this->projectName}" ) );
        
        $task = new phpucProjectDeleteTask();
        $task->setConsoleArgs( $this->args );
        $task->validate();
    }
    
    /**
     * Tests that {@link phpucProjectDeleteTask#validate()} fails with an
     * exception if no project configuration exists in the config.xml file.
     *
     * @return void
     */
    public function testValidateProjectDeleteWithoutProjectConfigurationFail()
    {
        $this->setExpectedException(
            'phpucValidateException',
            "Missing a project configuration for '{$this->projectName}'."
        );
        
        $projectName        = $this->projectName;
        $this->projectName .= '_foo';
        
        $this->createCCConfig();
        
        $this->projectName = $projectName;
        
        $this->createTestDirectories( array( "/projects/{$this->projectName}" ) );
        
        $task = new phpucProjectDeleteTask();
        $task->setConsoleArgs( $this->args );
        $task->validate();
    }
    
    /**
     * Tests that {@link phpucProjectDeleteTask#execute()} deletes the configuration
     * section from the config.xml file and that it deletes the project directories.
     * 
     * @return void
     */
    public function testExecuteDeleteProjectConfigChangesAndProjectDirectories()
    {
        $file = $this->createCCConfig();
        
        $this->createTestFile( "/{$this->projectName}.ser" );
        $this->createTestDirectories(
            array(
                "/artifacts/{$this->projectName}/foo",
                "/logs/{$this->projectName}/foo",
                "/projects/{$this->projectName}/foo",
            )
        );
        
        $task = new phpucProjectDeleteTask();
        $task->setConsoleArgs( $this->args );
        $task->execute();
        
        $this->assertFileNotExists( PHPUC_TEST_DIR . "/{$this->projectName}.ser" );
        $this->assertFileNotExists( PHPUC_TEST_DIR . "/artifacts/{$this->projectName}" );
        $this->assertFileNotExists( PHPUC_TEST_DIR . "/logs/{$this->projectName}" );
        $this->assertFileNotExists( PHPUC_TEST_DIR . "/projects/{$this->projectName}" );
        
        $this->assertFileExists( PHPUC_TEST_DIR . '/artifacts/' );
        $this->assertFileExists( PHPUC_TEST_DIR . '/logs/' );
        $this->assertFileExists( PHPUC_TEST_DIR . '/projects/' );
        
        $dom = new DOMDocument();
        $dom->load( $file );
        
        $xpath = new DOMXPath( $dom );
        $nodes = $xpath->query( "/cruisecontrol/project[@name='{$this->projectName}']" );

        $this->assertEquals( 0, $nodes->length );
        
    }
}