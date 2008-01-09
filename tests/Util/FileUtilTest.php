<?php
/**
 * This file is part of phpUnderControl.
 *
 * Copyright (c) 2007-2008, Manuel Pichler <mapi@manuel-pichler.de>.
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
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

require_once dirname( __FILE__ ) . '/../AbstractTest.php';

/**
 * Test cases for the file utility class.
 *
 * @package   Util
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
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
        
        if ( stripos( PHP_OS, 'win' ) === false )
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
        try
        {
            phpucFileUtil::findExecutable( 'cvs' );
            $this->fail( 'phpucErrorException expected.' );
        }
        catch ( phpucErrorException $e ) {}        
    }
    
    /**
     * Tests the find executable method on a faked windows system.
     *
     * @return void
     */
    public function testFindExecutableWindows()
    {
        $this->initExecutableTest( phpucFileUtil::OS_WINDOWS );
        $this->assertEquals( 'svn', phpucFileUtil::findExecutable( 'svn' ) );
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
        try
        {
            phpucFileUtil::findExecutable( 'cvs' );
            $this->fail( 'phpucErrorException expected.' );
        }
        catch ( phpucErrorException $e ) {}        
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