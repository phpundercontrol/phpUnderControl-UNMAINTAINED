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

require_once dirname( __FILE__ ) . '/AbstractPearTaskTest.php';

/**
 * Test case for the php code sniffer task.
 *
 * @package   Tasks
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucPHPUnitTaskTest extends phpucAbstractPearTaskTest
{
    /**
     * Content for a fake phpunit bin that works.
     *
     * @type string
     * @var string $validBin
     */
    protected $validBin = "#!/usr/bin/env php\n<?php echo 'version 3.2.0';?>";

    /**
     * Content for a fake phpunit bin that doesn't work.
     *
     * @type string
     * @var string $invalidBin
     */
    protected $invalidBin = "#!/usr/bin/env php\n<?php echo 'version 3.1.9';?>";

    /**
     * Content for a fake phpunit bin that returns an invalid version.
     *
     * @type string
     * @var string $badBin
     */
    protected $badBin = "#!/usr/bin/env php\n<?php echo ' version-3.2.0';?>";

    /**
     * Optional list of command line options.
     *
     * @type array<string>
     * @var array(string) $options
     */
    protected $options = array(
        '--test-case',
        'PhpUnderControl_Example_MathTest',
        '--test-dir',
        'tests',
        '--test-file',
        'MathTest.php'
    );

    /**
     * Sets the required binary contents.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->clearTestContents(  PHPUC_TEST_DIR . '/projects' );
        $this->clearTestContents(  PHPUC_TEST_DIR . '/build' );
        $this->clearTestContents(  PHPUC_TEST_DIR . '/logs' );

        if ( phpucFileUtil::getOS() === phpucFileUtil::OS_WINDOWS )
        {
            $this->badBin     = "@echo off\n\recho version-3.2.0";
            $this->validBin   = "@echo off\n\recho version 3.2.0";
            $this->invalidBin = "@echo off\n\recho version 3.1.9";
        }
    }

    /**
     * Tests validate with the required phpunit version.
     *
     * @return void
     */
    public function testPHPUnitVersionValidate()
    {
        $this->createExecutable( 'phpunit', $this->validBin );
        $phpunit = new phpucPhpUnitTask();
        $phpunit->setConsoleArgs( $this->args );
        $phpunit->validate();
    }

    /**
     * Tests that the execute method adds a correct build file target.
     *
     * @return void
     */
    public function testPHPUnitExecuteBuildFileModifications()
    {
        $this->createCCConfig();

        $file = sprintf(
            '%s/projects/%s/build.xml',
            PHPUC_TEST_DIR,
            $this->projectName
        );

        $this->assertFileNotExists( $file );

        $this->createExecutable( 'phpunit', $this->validBin );

        $phpunit = new phpucPhpUnitTask();
        $phpunit->setConsoleArgs( $this->args );
        $phpunit->validate();
        $phpunit->execute();

        $this->assertFileExists( $file );

        $dom = new DOMDocument();
        $dom->load( $file );

        $xpath = new DOMXPath( $dom );
        $result = $xpath->query( '//target[@name="phpunit"]/exec/@executable' );

        $this->assertEquals( 1, $result->length );

        // Check that the executable path begins with the test bin directory.
        $this->assertEquals(
            0, strpos( $result->item( 0 )->nodeValue, PHPUC_TEST_DIR . '/bin/phpunit' )
        );
    }

    /**
     * Tests that the phpunit task adds an artifact publisher into the
     * project configuration.
     *
     * @return void
     */
    public function testPHPUnitExecuteConfigFileWithoutArtifactsDirectory()
    {
        $node = $this->prepareTestAndReturnsPublisherNode();

        $this->assertEquals(
            'projects/${project.name}/build/coverage',
            $node->getAttribute( 'dir' )
        );
        $this->assertEquals(
            'logs/${project.name}', $node->getAttribute( 'dest' )
        );
        $this->assertEquals(
            'coverage', $node->getAttribute( 'subdirectory' )
        );
    }

    /**
     * Tests that the phpunit task adds an artifact publisher into the
     * project configuration and uses the artifacts directory for the generated
     * coverage report.
     *
     * @return void
     */
    public function testPHPUnitExecuteConfigFileWithArtifactsDirectory()
    {
        $dirs = array( "artifacts/{$this->projectName}" );
        $node = $this->prepareTestAndReturnsPublisherNode( $dirs );

        $this->assertEquals(
            'projects/${project.name}/build/coverage',
            $node->getAttribute( 'dir' )
        );
        $this->assertEquals(
            'artifacts/${project.name}', $node->getAttribute( 'dest' )
        );
        $this->assertEquals(
            'coverage', $node->getAttribute( 'subdirectory' )
        );
    }

    /**
     * Tests that the validate method throws an exception for a not found
     * executable.
     *
     * @return void
     */
    public function testValidateFindPHPUnitExecutableFail()
    {
        $phpunit = new phpucPhpUnitTask();
        $phpunit->setConsoleArgs( $this->args );

        $this->setExpectedException( 'phpucValidateException' );
        $phpunit->validate();
    }

    /**
     * This test runs with phpunit, so there should be a global the phpunit
     * executable.
     *
     * @return void
     */
    public function testValidateFindPHPUnitExecutableInPath()
    {
        $this->prepareArgv( array( 'example', PHPUC_TEST_DIR ) );

        $input = new phpucConsoleInput();
        $input->parse();

        $phpunit = new phpucPhpUnitTask();
        $phpunit->setConsoleArgs( $input->args );
        $phpunit->validate();

        $this->assertNotNull( $phpunit->executable );
    }

    /**
     * Executes the phpunit task and return the created artifactspublisher node.
     *
     * @param array(string) $dirs Optional list of test directories.
     *
     * @return DOMElement The artifactspublisher element
     */
    protected function prepareTestAndReturnsPublisherNode( array $dirs = array() )
    {
        // Create dummy cc config
        $this->createCCConfig();

        // Create dummy phpunit executable
        $this->createExecutable( 'phpunit', $this->validBin );

        // Append project log directory
        $dirs[] = "logs/{$this->projectName}";

        // Create test directories
        $this->createTestDirectories( $dirs );

        $phpunit = new phpucPhpUnitTask();
        $phpunit->setConsoleArgs( $this->args );
        $phpunit->validate();
        $phpunit->execute();

        $dom = new DOMDocument();
        $dom->load( PHPUC_TEST_DIR . '/config.xml' );

        $xpath  = new DOMXPath( $dom );
        $result = $xpath->query(
            sprintf(
                '/cruisecontrol/project[
                   @name="%s"
                 ]/publishers/artifactspublisher[@subdirectory="coverage"]',
                $this->projectName
            )
        );

        $this->assertEquals( 1, $result->length );

        return $result->item( 0 );
    }

    /**
     * This test checks whether --without-phpunit option has been set properly
     *
     * @covers phpucPhpUnitTask::registerCommandExtension
     *
     * @return void
     */
    public function testPHPUnitTaskIsIgnored()
    {
        $this->prepareArgv(
            array( 'example', PHPUC_TEST_DIR, '--without-phpunit' )
        );

        $input = new phpucConsoleInput();
        $input->parse();

        $command = phpucAbstractCommand::createCommand(
                    $input->args->command
        );
        $command->setConsoleArgs( $input->args );

        $cmdTasks = $command->createTasks();

        $this->assertPhpucTaskNotOnTheList( $cmdTasks, 'phpucPhpUnitTask' );
    }
}
