<?php
/**
 * This file is part of phpUnderControl.
 * 
 * PHP Version 5.2.0
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
 * @package   Console
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id: PhpUnderControl.php 1782 2007-11-28 13:34:46Z mapi $
 * @link      http://www.phpundercontrol.org/
 */

require_once dirname( __FILE__ ) . '/../AbstractTest.php';

/**
 * Test case for the console arguments.
 *
 * @category  QualityAssurance
 * @package   Console
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucConsoleArgsTest extends phpucAbstractTest
{
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
        
        $input = new phpucConsoleInput();
        $input->parse();
        
        $args = $input->args;
        
        $this->assertTrue( $args->hasArgument( 'cc-install-dir' ) );
        $this->assertEquals(
            '/opt/cruisecontrol',
            $args->getArgument( 'cc-install-dir' )
        );
        $this->assertEquals( 1, count( $args->arguments ) );
        
        // Every other argument request must result in an OutOfRangeException
        try
        {
            $args->getArgument( 'phpUnderControl' );
            $this->fail( 'OutOfRangeException expected.' );
        }
        catch ( OutOfRangeException $e ) 
        {
            
        }
    }
    
    /**
     * Tests that {@link phpucConsoleArgs#getOption()} fails with an exception 
     * for an unknown option identifier.
     * 
     * @return void
     */
    public function testConsoleArgsWithUnknownOptionFail()
    {
        $this->setExpectedException(
            'OutOfRangeException', 'Unknown option "phpuc".'
        );
        
        $args = new phpucConsoleArgs( 'phpuc', array(), array() );
        $args->getOption( 'phpuc' );
    }
    
    /**
     * Tests that {@link phpucConsoleArgs#setOption()} works as expected.
     *
     * @return void
     */
    public function testConsoleArgsSetOption()
    {
        $args = new phpucConsoleArgs( 'phpuc', array(), array() );
        $this->assertFalse( $args->hasOption( 'phpuc' ) );
        $args->setOption( 'phpuc', 'phpUnderControl' );
        $this->assertTrue( $args->hasOption( 'phpuc' ) );
        $this->assertEquals( 'phpUnderControl', $args->getOption( 'phpuc' ) );
    }
    
    /**
     * Tests that {@link phpucConsoleArgs::__get()} fails with an exception for
     * an unknown property.
     *
     * @return void
     */
    public function testConsoleArgsMagicGetterWithUnknownPropertyFail()
    {
        $this->setExpectedException(
            'OutOfRangeException', 
            'Unknown or writonly property $phpUnderControl.'
        );
        
        $args  = new phpucConsoleArgs( 'phpuc', array(), array() );
        $value = $args->phpUnderControl;
    }
    
}