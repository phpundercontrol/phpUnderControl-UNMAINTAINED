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
 * @author    Sebastian Marek <proofek@gmail.com>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://www.phpundercontrol.org/
 */

require_once dirname( __FILE__ ) . '/../AbstractTest.php';

/**
 * Test case for the install cruisecontrol script task.
 *
 * @package   Tasks
 * @author    Sebastian Marek <proofek@gmail.com>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucInstallCcScriptTaskTest extends phpucAbstractTest
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

        $this->setUpVfsStream();
    }

    /**
     * Tests validation for {@link phpucInstallCcScriptTask}
     *
     * @dataProvider validationDataProvider
     * @covers phpucInstallCcScriptTask::validate
     * @return void
     */
    public function testValidateInstallCcScriptTask( $args, $success, $exception, $message )
    {
        $this->createCruiseControlDummy();

        $this->prepareArgv( $args );

        $input = new phpucConsoleInput();
        $input->parse();

        $this->args = $input->args;

        $task = new phpucInstallCcScriptTask();
        $task->setConsoleArgs( $this->args );

        if ( !$success ) {

            $this->setExpectedException( $exception, $message );
        }

        $task->validate();
    }

    /**
     * @covers phpucInstallCcScriptTask::execute
     * @dataProvider executeDataProvider
     */
    public function testExecute( $opts )
    {
        $this->createCruiseControlDummy();
        $this->createInitScriptsDirectory();

        $ccInstallDir = vfsStream::url( 'opt/cruisecontrol' );
        $initDir = PHPUC_TEST_DIR . '/init.d';
        $javaHome = '/usr';
        $ccBin = 'cruisecontrol.sh';
        $ccUser = 'cruisecontrol';

        $args = array();
        $args[] = 'setup-cc';
        if ( isset( $opts['cc-bin'] ) ) {
            $ccBin = $opts['cc-bin'];
            $args[] = '--cc-bin';
            $args[] = $ccBin;
        }
        if ( isset( $opts['cc-user'] ) ) {
            $ccUser = $opts['cc-user'];
            $args[] = '--cc-user';
            $args[] = $ccUser;
        }
        if ( isset( $opts['java-home'] ) ) {
            $javaHome = $opts['java-home'];
            $args[] = '--java-home';
            $args[] = $javaHome;
        }
        $args[] = $ccInstallDir;
        $args[] = $initDir;

        $this->prepareArgv( $args );

        $input = new phpucConsoleInput();
        $input->parse();

        $this->args = $input->args;

        $task = new phpucInstallCcScriptTask();
        $task->setConsoleArgs( $this->args );

        $task->execute();

        $this->assertTrue( file_exists( "$initDir/cruisecontrol" ) );

        $startScript = file_get_contents( "$initDir/cruisecontrol" );
        $matches = array();

        preg_match( '/CC_INSTALL_DIR=(.*)/', $startScript, $matches );
        $this->assertEquals(
            $ccInstallDir,
            $matches[1]
        );

        preg_match( '/CC_BIN=(.*)/', $startScript, $matches );
        $this->assertEquals(
            $ccBin,
            $matches[1]
        );

        preg_match( '/JAVA_HOME=(.*)/', $startScript, $matches );
        $this->assertEquals(
            $javaHome,
            $matches[1]
        );

        preg_match( '/RUNASUSER=(.*)/', $startScript, $matches );
        $this->assertEquals(
            $ccUser,
            $matches[1]
        );
    }

    public function validationDataProvider()
    {
        if ( !$this->setUpVfsStream() ) {

            return false;
        }

        return array(
            array( array(
                    'setup-cc',
                    '--cc-bin',
                    'cruisecontrol.sh',
                    vfsStream::url( 'opt/cruisecontrol' ),
                    vfsStream::url( 'etc1/init.d' ),
                ), true, null, null
            ),
            array( array(
                    'setup-cc',
                    '--cc-bin',
                    'cruisecontrol.sh',
                    vfsStream::url( 'usr/share/cruisecontrol' ),
                    vfsStream::url( 'etc/init.d' )
                ), false, 'phpucValidateException', 'Invalid cruisecontrol directory <cc-install-dir>.'
            ),
            array( array(
                    'setup-cc',
                    '--cc-bin',
                    'notexistingscript.sh',
                    vfsStream::url( 'opt/cruisecontrol' ),
                    vfsStream::url( 'etc/init.d' )
                ), false, 'phpucValidateException', 'Invalid cruisecontrol directory <cc-install-dir>.'
            ),
            array( array(
                    'setup-cc',
                    '--cc-bin',
                    'cruisecontrol.sh',
                    vfsStream::url( 'opt/cruisecontrol' ),
                    vfsStream::url( 'etc/noInit.d' )
                ), false, 'phpucValidateException', 'Invalid init scripts directory <init-dir>.'
            ),
            array( array(
                    'setup-cc',
                    '--cc-bin',
                    'cruisecontrol.sh',
                    '--java-home',
                    vfsStream::url( 'opt/noJava' ),
                    vfsStream::url( 'opt/cruisecontrol' ),
                    vfsStream::url( 'etc/init.d' )
                ), false, 'phpucValidateException', 'Invalid JAVA_HOME directory.'
            ),
            array( array(
                    'setup-cc',
                    '--cc-bin',
                    'cruisecontrol.sh',
                    '--java-home',
                    vfsStream::url( 'usr/sbin' ),
                    vfsStream::url( 'opt/cruisecontrol' ),
                    vfsStream::url( 'etc/init.d' )
                ), false, 'phpucValidateException', 'Invalid JAVA_HOME directory.'
            ),
            array( array(
                    'setup-cc',
                    '--cc-bin',
                    'cruisecontrol.sh',
                    '--java-home',
                    vfsStream::url( 'usr' ),
                    vfsStream::url( 'opt/cruisecontrol' ),
                    vfsStream::url( 'etc/init.d' )
                ), false, 'phpucValidateException', 'Cruisecontrol init script already exists - Aborting!'
            ),
            array( array(
                    'setup-cc',
                    '--cc-bin',
                    'cruisecontrol.sh',
                    '--java-home',
                    vfsStream::url( 'usr' ),
                    vfsStream::url( 'opt/cruisecontrol' ),
                    vfsStream::url( 'etc2/init.d' )
                ),
        false,
        'phpucValidateException',
        'Cannot write into init script directory. ' .
                'Make sure the directory is writeable or ' .
                'run the script as root user.'
            ),
        );
    }

    public function executeDataProvider()
    {
        if ( !$this->setUpVfsStream() ) {

            return false;
        }

        return array(
            array(
                'cc-bin' => 'cc.sh',
            ),
            array(
                'cc-bin' => 'cc.sh',
                'cc-user' => 'phpuc',
            ),
            array(
                'cc-bin' => 'cc.sh',
                'cc-user' => 'phpuc',
                'java-home' => vfsStream::url( 'usr' ),
            ),
            array( false )
        );
    }

    /**
     * Creates a dummy CruiseControl directory structure and returns the root
     * directory of that installation.
     */
    protected function createCruiseControlDummy()
    {
        $this->createVfsDirectories(
            array(
                'usr/sbin',
                'etc/init.d',
                'opt/cruisecontrol',
                'usr/bin',
                'etc1/init.d',
                'etc2/init.d'
            )
        );
        $this->createVfsFile( 'opt/cruisecontrol/cruisecontrol.sh' );
        $this->createVfsFile( 'usr/bin/java' );
        $this->createVfsFile( 'etc/init.d/cruisecontrol' );

        vfsStreamWrapper::getRoot()->getChild( 'etc2/init.d' )->chmod (0555 );
    }
}
