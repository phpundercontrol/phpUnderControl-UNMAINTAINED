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

require_once dirname( __FILE__ ) . '/AbstractPearTaskTest.php';

/**
 * Test case for the {@link phpucPhpDocumentorTask}.
 *
 * @category  QualityAssurance
 * @package   Tasks
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucPhpDocumentorTaskTest extends phpucAbstractPearTaskTest
{
    /**
     * Content for a fake phpdoc bin that works.
     *
     * @type string
     * @var string $validBin
     */
    protected $validBin = "#!/usr/bin/env php\n<?php echo 'version 1.2.0';?>";

    /**
     * Sets the required binary contents.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        if ( phpucFileUtil::getOS() === phpucFileUtil::OS_WINDOWS )
        {
            $this->validBin = "@echo off\n\recho version 1.2.0";
        }
    }

    /**
     * Tests that the phpdoc task adds an artifact publisher into the
     * project configuration.
     *
     * @return void
     */
    public function testPHPDocumentorExecuteConfigFileWithoutArtifactsDirectory()
    {
        $node = $this->prepareTestAndReturnsPublisherNode();

        $this->assertEquals(
            'projects/${project.name}/build/api', $node->getAttribute( 'dir' )
        );
        $this->assertEquals(
            'logs/${project.name}', $node->getAttribute( 'dest' )
        );
        $this->assertEquals( 'api', $node->getAttribute( 'subdirectory' ) );
    }

    /**
     * Tests that the phpdoc task adds an artifact publisher into the
     * project configuration and uses the artifacts directory for the generated
     * api documentation.
     *
     * @return void
     */
    public function testPHPDocumentorExecuteConfigFileWithArtifactsDirectory()
    {
        $dirs = array( "artifacts/{$this->projectName}" );
        $node = $this->prepareTestAndReturnsPublisherNode( $dirs );

        $this->assertEquals(
            'projects/${project.name}/build/api', $node->getAttribute( 'dir' )
        );
        $this->assertEquals(
            'artifacts/${project.name}', $node->getAttribute( 'dest' )
        );
        $this->assertEquals( 'api', $node->getAttribute( 'subdirectory' ) );
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

        // Append project log directory
        $dirs[] = "logs/{$this->projectName}";

        // Create test directories
        $this->createTestDirectories( $dirs );

        // Create dummy phpdoc executable
        $this->createExecutable( 'phpdoc', $this->validBin );

        $phpdoc = new phpucPhpDocumentorTask();
        $phpdoc->setConsoleArgs( $this->args );
        $phpdoc->validate();
        $phpdoc->execute();

        $dom = new DOMDocument();
        $dom->load( PHPUC_TEST_DIR . '/config.xml' );

        $xpath  = new DOMXPath( $dom );
        $result = $xpath->query(
            sprintf(
                '/cruisecontrol/project[
                   @name="%s"
                 ]/publishers/artifactspublisher[@subdirectory="api"]',
                $this->projectName
            )
        );

        $this->assertEquals( 1, $result->length );

        return $result->item( 0 );
    }

    /**
     * This test checks whether --without-php-documentor option has been set properly
     *
     * @covers phpucPhpDocumentorTask::registerCommandExtension
     *
     * @return void
     */
    public function testPhpDocumentorTaskIsIgnored()
    {
        $this->prepareArgv(
            array( 'example', PHPUC_TEST_DIR, '--without-php-documentor' )
        );

        $input = new phpucConsoleInput();
        $input->parse();

        $command = phpucAbstractCommand::createCommand(
                    $input->args->command
        );
        $command->setConsoleArgs( $input->args );

        $cmdTasks = $command->createTasks();

        $this->assertPhpucTaskNotOnTheList( $cmdTasks, 'phpucPhpDocumentorTask' );
    }
}