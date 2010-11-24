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
 * @package   Util
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

require_once dirname( __FILE__ ) . '/../AbstractTest.php';

/**
 * Test cases for the file utility class.
 *
 * @package   Util
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucFileUtilTest extends phpucAbstractTest
{
    /**
     * The current operation system.
     *
     * @type integer
     * @var integer $os
     */
    protected $os = null;

    /**
     * Detects the operation system.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        if ( stripos( PHP_OS, 'win' ) === false || stristr( PHP_OS, 'darwin' ) )
        {
            $this->os = phpucFileUtil::OS_UNIX;
        }
        else
        {
            $this->os = phpucFileUtil::OS_WINDOWS;
        }
    }

    /**
     * Tests that the {@link phpucFileUtil::getOS()} method detects the correct
     * operation system.
     *
     * @return void
     */
    public function testGetOperationSystem()
    {
        $this->assertEquals( $this->os, phpucFileUtil::getOS() );
    }

    /**
     * Tests that the {@link phpucFileUtil::getPaths()} method returns an array
     * with all directories from <b>PATH</b> environment variable.
     *
     * @return void
     */
    public function testGetEnvironmentPaths()
    {
        $env   = array_flip( explode( PATH_SEPARATOR, getenv( 'PATH' ) ) );
        $paths = phpucFileUtil::getPaths();

        $this->assertType( 'array', $paths );

        foreach ( $paths as $path )
        {
            $this->assertArrayHasKey( $path, $env );
            unset( $env[$path] );
        }

        $this->assertEquals( 0, count( $env ) );
    }

    /**
     * Tests the find executable method on a faked unix system.
     *
     * @return void
     */
    public function testFindExecutableUnix()
    {
        if ( self::$windows )
        {
            $this->markTestSkipped( 'Windows doesn\'t support this test.' );
            return;
        }
        $this->initExecutableTest();
        $this->assertEquals( 'svn', phpucFileUtil::findExecutable( 'svn' ) );
    }

    /**
     * Tests that an exceptions is thrown if a requested executable doesn't
     * exist.
     *
     * @return void
     */
    public function testFindExecutableUnixFail()
    {
        if ( self::$windows )
        {
            $this->markTestSkipped( 'Windows doesn\'t support this test.' );
            return;
        }
        $this->initExecutableTest();

        $this->setExpectedException( 'phpucErrorException' );

        phpucFileUtil::findExecutable( 'cvs' );
    }

    /**
     * Tests the find executable method on a faked windows system.
     *
     * @return void
     */
    public function testFindExecutableWindows()
    {
        $this->initExecutableTest( phpucFileUtil::OS_WINDOWS );
        $this->assertEquals(
            'svn.cmd', basename( phpucFileUtil::findExecutable( 'svn' ) )
        );
    }

    /**
     * Tests that an exceptions is thrown if a requested executable doesn't
     * exist.
     *
     * @return void
     */
    public function testFindExecutableWindowsFail()
    {
        $this->initExecutableTest( phpucFileUtil::OS_WINDOWS );

        $this->setExpectedException( 'phpucErrorException' );

        phpucFileUtil::findExecutable( 'cvs' );
    }

    /**
     * Tests the recursive delete implementation works as expected.
     *
     * @return void
     */
    public function testDeleteDirectory()
    {
        $this->createTestDirectories(
            array(
                '/artifacts/foo/12345',
                '/artifacts/foo/67890',
            )
        );

        $this->createTestFile( '/artifacts/foo/12345/bar.txt' );
        $this->createTestFile( '/artifacts/foo/67890/bar.txt' );
        $this->createTestFile( '/artifacts/foo/bar.txt' );
        $this->createTestFile( '/artifacts/bar.txt' );

        phpucFileUtil::deleteDirectory( PHPUC_TEST_DIR . '/artifacts' );

        $this->assertFileNotExists( PHPUC_TEST_DIR . '/artifacts' );
    }

    /**
     * Tests the recursive delete implementation works also for directories with
     * linked contents.
     *
     * @return void
     */
    public function testDeleteDirectoryWithLinksAndSymlinks()
    {
        if ( !function_exists( 'link' ) || !function_exists( 'symlink' ) )
        {
            $this->markTestSkipped( 'Missing "link" or "symlink" function.' );
            return;
        }

        $this->createTestDirectories( array( '/logs/foo/12345' ) );
        $this->createTestFile( '/logs/foo/12345/bar.txt' );

        $file = PHPUC_TEST_DIR . '/logs/foo/12345/bar.txt';

        link( $file, PHPUC_TEST_DIR . '/logs/bar.txt' );
        symlink( $file, PHPUC_TEST_DIR . '/logs/foo/bar.txt' );

        phpucFileUtil::deleteDirectory( PHPUC_TEST_DIR . '/logs' );

        $this->assertFileNotExists( PHPUC_TEST_DIR . '/logs' );
    }

    /**
     * Initializes the test directories and files for the executable test.
     *
     * @param integer $os The operation system.
     *
     * @return void
     */
    protected function initExecutableTest( $os = phpucFileUtil::OS_UNIX )
    {
        // Set operation system to unix
        phpucFileUtil::setOS( $os );
        // Set fake environment directories
        phpucFileUtil::setPaths(
            $this->createTestDirectories(
                array(
                    '/usr/bin',
                    '/usr/local/bin'
                )
            )
        );
        if ( $os === phpucFileUtil::OS_WINDOWS )
        {
            $this->createTestFile( '/usr/bin/svn.cmd' );
        }
        else
        {
            $this->createTestFile( '/usr/bin/svn' );
        }
    }
}
