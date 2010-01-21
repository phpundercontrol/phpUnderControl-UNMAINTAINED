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
class phpucPhpCodeSnifferTaskTest extends phpucAbstractPearTaskTest
{
    /**
     * Content for a fake code sniffer bin that works.
     *
     * @type string
     * @var string $validBin
     */
    protected $validBin = "#!/usr/bin/env php\n<?php echo 'version 1.0.0';?>";

    /**
     * Content for a fake code sniffer bin that doesn't work.
     *
     * @type string
     * @var string $invalidBin
     */
    protected $invalidBin = "#!/usr/bin/env php\n<?php echo 'version 1.0.0RC2';?>";

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
            $this->validBin   = "@echo off\n\recho version 1.0.0";
            $this->invalidBin = "@echo off\n\recho version 1.0.0RC2";
        }
    }

    /**
     * Tests validate with the required code sniffer version.
     *
     * @return void
     */
    public function testCodeSnifferVersionValidate()
    {
        $this->createExecutable( 'phpcs', $this->validBin );
        $phpcs = new phpucPhpCodeSnifferTask();
        $phpcs->setConsoleArgs( $this->args );
        $phpcs->validate();
    }

    /**
     * Tests that the validate method fails for an unsupported code sniffer
     * version.
     *
     * @return void
     */
    public function testCodeSnifferVersionValidateWithInvalidVersion()
    {
        $this->createExecutable( 'phpcs', $this->invalidBin );
        $phpcs = new phpucPhpCodeSnifferTask();
        $phpcs->setConsoleArgs( $this->args );

        $this->setExpectedException( 'phpucValidateException' );
        $phpcs->validate();
    }

    /**
     * Tests that the execute method adds a correct build file target.
     *
     * @return void
     */
    public function testCodeSnifferExecuteBuildFileModifications()
    {
        $phpcs = new phpucPhpCodeSnifferTask();
        $phpcs->setConsoleArgs( $this->args );
        $phpcs->execute();

        $sxml = simplexml_load_file( $this->projectDir . '/build.xml' );
        $build = $sxml->xpath( '/project/target[@name="build"]' );
        $phpcs = $sxml->xpath( '/project/target[@name="php-codesniffer"]' );

        $this->assertEquals( 1, count( $phpcs ) );
        $this->assertEquals( 'php-codesniffer', (string) $build[0]['depends'] );
        $this->assertEquals( 'php-codesniffer', (string) $phpcs[0]['name'] );
    }

    /**
     * This test checks whether --without-code-sniffer option has been set properly
     *
     * @covers phpucPhpCodeSnifferTask::registerCommandExtension
     *
     * @return void
     */
    public function testPhpCodeSnifferTaskIsIgnored()
    {
        $this->prepareArgv(
            array( 'example', PHPUC_TEST_DIR, '--without-code-sniffer' )
        );

        $input = new phpucConsoleInput();
        $input->parse();

        $command = phpucAbstractCommand::createCommand(
                    $input->args->command
        );
        $command->setConsoleArgs( $input->args );

        $cmdTasks = $command->createTasks();

        $this->assertPhpucTaskNotOnTheList( $cmdTasks, 'phpucPhpCodeSnifferTask' );
    }
}
