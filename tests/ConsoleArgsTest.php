<?php
/**
 * This file is part of phpUnderControl.
 *
 * Copyright (c) 2007, Manuel Pichler <mapi@manuel-pichler.de>.
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
 * @package   phpUnderControl
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id: PhpUnderControl.php 1782 2007-11-28 13:34:46Z mapi $
 * @link      http://www.phpunit.de/wiki/phpUnderControl
 */

require_once dirname( __FILE__ ) . '/AbstractTest.php';

/**
 * Test case for the console arguments.
 *
 * @package   phpUnderControl
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpunit.de/wiki/phpUnderControl
 */
class phpucConsoleArgsTest extends phpucAbstractTest
{
    /**
     * Tests that the console arg ctor throws an exception if no $argv variable
     * exists.
     *
     * @return void
     */
    public function testConsoleWithoutArgv()
    {
        $this->prepareArgv();
        
        try
        {
            new phpucConsoleArgs();
            $this->fail( 'phpucConsoleException expected.' );
        }
        catch ( phpucConsoleException $e ) {}
    }
    
    /**
     * Tests the console args object without options. It also checks that only
     * the defined arguments are present.
     *
     * @return void
     */
    public function testConsoleInstallCommandWithoutOptions()
    {
        $this->prepareArgv( 
            array( 
                'install', 
                '/opt/cruisecontrol',
                'dummy-argument'
            )
        );
        
        $console = new phpucConsoleArgs();
        $console->parse();
        
        $this->assertTrue( 
            $console->hasArgument( 'cc-install-dir' ) 
        );
        $this->assertEquals(
            '/opt/cruisecontrol',
            $console->getArgument( 'cc-install-dir' )
        );
        $this->assertEquals( 1, count( $console->arguments ) );
        
        // Every other argument request must result in an OutOfRangeException
        try
        {
            $console->getArgument( 'phpUnderControl' );
            $this->fail( 'OutOfRangeException expected.' );
        }
        catch ( OutOfRangeException $e ) {}
    }
    
    /**
     * Tests the install command without the cc-install-dir argument which must
     * result in an {@link phpucConsoleException}.
     *
     * @return void
     */
    public function testConsoleInstallCommandButWithoutArguments()
    {
        $this->prepareArgv( array( 'install' ) );
        
        $console = new phpucConsoleArgs();
        
        try
        {
            $console->parse();
            $this->fail( 'phpucConsoleException expected.' );
        }
        catch ( phpucConsoleException $e ) {}
    }
    
    /**
     * Tests the console arg object with the example command and the build 
     * system option.
     *
     * @return void
     */
    public function testConsoleExampleCommandWithBuildSystemOption()
    {
        $this->prepareArgv(
            array( 'example', '--build-system', 'ant', '/opt/cruisecontrol' )
        );
        
        $console = new phpucConsoleArgs();
        $console->parse();
        
        $this->assertTrue( $console->hasOption( 'build-system' ) );
        $this->assertEquals( 'ant', $console->getOption( 'build-system' ) );
        
        try
        {
            $console->getOption( 'phpUnderControl' );
            $this->fail( 'OutOfRangeException expected.' );
        }
        catch ( OutOfRangeException $e ) {}
    }
    
    /**
     * Tests that the console mapping between short and long options.
     *
     * @return void
     */
    public function testConsoleExampleCommandWithBuildSystemShortOption()
    {
        $this->prepareArgv( 
            array( 'example', '-b', 'ant', '/opt/cruisecontrol' )
        );
        
        $console = new phpucConsoleArgs();
        $console->parse();
        
        $this->assertTrue( $console->hasOption( 'build-system' ) );
        $this->assertEquals( 'ant', $console->getOption( 'build-system' ) );        
    }
    
    /**
     * Tests that the default value for the mandatory "--build-system" is set by
     * the console args object.
     *
     * @return void
     */
    public function testConsoleExampleWithoutOptionExpectedAnt()
    {
        $this->prepareArgv( 
            array( 'example', '/opt/cruisecontrol' )
        );
        
        $console = new phpucConsoleArgs();
        $console->parse();
        
        $this->assertTrue( $console->hasOption( 'build-system' ) );
        $this->assertEquals( 'ant', $console->getOption( 'build-system' ) );  
    }
    
    /**
     * Tests that the parse method throws an {@link phpucConsoleException} for
     * invalid command identifiers.
     *
     * @return void
     */
    public function testConsoleWithInvalidCommandIdentifier()
    {
        $this->prepareArgv( array( 'phpUnderControl' ) );
        $console = new phpucConsoleArgs();
        
        ob_start();
        
        try
        {
            $console->parse();
            $this->fail( 'phpucConsoleException expected.' );
        }
        catch ( phpucConsoleException $e ) {}
        
        ob_end_clean();
    }
    
    public function testConsolePrintHelp()
    {
        $this->prepareArgv( array( '-h' ) );
        $console = new phpucConsoleArgs();
        
        ob_start();
        $console->parse();
        $content = ob_get_contents();
        ob_end_clean();
        
        $this->assertRegExp( '/Command line options and arguments for "\w+"/', $content );
    }
}