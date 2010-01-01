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
 * @package   Console
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

require_once dirname( __FILE__ ) . '/../AbstractTest.php';

/**
 * Test case for the console input definition class.
 *
 * @category  QualityAssurance
 * @package   Console
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucConsoleInputDefinitionTest extends phpucAbstractTest
{
    /**
     * Tests the {@link phpucConsoleInputDefinition::addCommand()} method with
     * a new command.
     *
     * @return void
     */
    public function testAddCommand()
    {
        $definition = new phpucConsoleInputDefinition();
        $this->assertFalse( $definition->hasCommand( 'mapi' ) );
        $definition->addCommand( 'mapi', 'Hello World' );
        $this->assertTrue( $definition->hasCommand( 'mapi' ) );
    }
    
    /**
     * Tests that {@link phpucConsoleInputDefinition::addCommand()} fails with
     * an exception if the same command identifier is used a second time. 
     *
     * @return void
     */
    public function testAddCommandTwoTimesFail()
    {
        $this->setExpectedException(
            'phpucErrorException',
            "The command name 'mapi' is already in use."
        );
        
        $definition = new phpucConsoleInputDefinition();
        $definition->addCommand( 'mapi', 'Hello World 1.' );
        $definition->addCommand( 'mapi', 'Hello World 2.' );
    }
    
    /**
     * Tests that {@link phpucConsoleInputDefinition::addCommand()} fails with
     * an exception if an invalid mode value is passed in. 
     *
     * @return void
     */
    public function testAddCommandWithInvalidModeFail()
    {
        $definition = new phpucConsoleInputDefinition();
        $definition->addCommand( 
            'mapi0', 'Hello World.', phpucConsoleInputDefinition::MODE_HIDDEN 
        );
        $definition->addCommand( 
            'mapi1', 'Hello World.', phpucConsoleInputDefinition::MODE_NORMAL 
        );
        
        $this->setExpectedException(
            'phpucErrorException',
            'Invalid value for mode given.'
        );
        
        $definition = new phpucConsoleInputDefinition();
        $definition->addCommand( 'mapi2', 'Hello World.', -42 );
    }
    
    /**
     * Tests that {@link phpucConsoleInputDefinition::addArgument()} fails with
     * an exception if an unknown command identifier is passed in. 
     *
     * @return void
     */
    public function testAddArgumentWithUnknownCommandIdentifierFail()
    {
        $this->setExpectedException(
            'phpucErrorException',
            "The command 'manuel' for 'pichler' doesn't exist."
        );
        
        $definition = new phpucConsoleInputDefinition();
        $definition->addArgument( 'manuel', 'pichler', 'Hello World.' );
    }
    
    /**
     * Tests that {@link phpucConsoleInputDefinition::addArgument()} fails with
     * an exception if an argument with an equal identifier already exists. 
     *
     * @return void
     */
    public function testAddArgumentTwoTimesFail()
    {
        $this->setExpectedException(
            'phpucErrorException',
            "An argument 'pichler' for command 'manuel' already exists."
        );
        
        $definition = new phpucConsoleInputDefinition();
        $definition->addCommand( 'manuel', 'Hello World.' );
        $definition->addArgument( 'manuel', 'pichler', 'Hello World 1.' );
        $definition->addArgument( 'manuel', 'pichler', 'Hello World 2.' );
    }
    
    /**
     * Tests that {@link phpucConsoleInputDefinition::addArgument()} fails with
     * an exception if the mandatory parameter is not of type <b>boolean</b>. 
     *
     * @return void
     */
    public function testAddArgumentWithInvalidMandatoryValueFail()
    {
        $this->setExpectedException(
            'phpucErrorException',
            'The mandatory parameter must be of type boolean.'
        );
        
        $definition = new phpucConsoleInputDefinition();
        $definition->addCommand( 'manuel', 'Hello World.' );
        $definition->addArgument( 'manuel', 'pichler', 'Hello World.', null );
    }
    
    /**
     * Tests that {@link phpucConsoleInputDefinition::addArgument()} stores
     * a valid argument. 
     *
     * @return void
     */
    public function testAddArgument()
    {
        $definition = new phpucConsoleInputDefinition();
        $definition->addCommand( 'manuel', 'Hello World.' );
        
        $this->assertFalse( $definition->hasArgument( 'manuel', 'foo' ) );
        $definition->addArgument( 'manuel', 'foo', 'Hello World.' );
        $this->assertTrue( $definition->hasArgument( 'manuel', 'foo' ) );
    }
    
    /**
     * Tests that {@link phpucConsoleInputDefinition::addOption()} fails with an
     * exception if an unknown command identifier is passed in. 
     *
     * @return void
     */
    public function testAddOptionWithUnknownCommandIdentifierFail()
    {
        $this->setExpectedException(
            'phpucErrorException',
            "The command 'manuel' for option 'pichler' doesn't exist."
        );
        
        $definition = new phpucConsoleInputDefinition();
        $definition->addOption( 'manuel', 'p', 'pichler', 'Hello World.' );
    }
    
    /**
     * Tests that {@link phpucConsoleInputDefinition::addOption()} fails with an
     * exception if an option with an equal identifier already exists. 
     *
     * @return void
     */
    public function testAddOptionTwoTimesSameShortIdentifierFail()
    {
        $this->setExpectedException(
            'phpucErrorException',
            "An option 'p' already exists for command 'manuel'."
        );
        
        $definition = new phpucConsoleInputDefinition();
        $definition->addCommand( 'manuel', 'Hello World.' );
        $definition->addOption( 'manuel', 'p', 'pichler1', 'Hello World 1.' );
        $definition->addOption( 'manuel', 'p', 'pichler2', 'Hello World 2.' );
    }
    
    /**
     * Tests that {@link phpucConsoleInputDefinition::addOption()} fails with an
     * exception if an option with an equal identifier already exists. 
     *
     * @return void
     */
    public function testAddOptionTwoTimesSameLongIdentifierFail()
    {
        $this->setExpectedException(
            'phpucErrorException',
            "An option 'pichler' already exists for command 'manuel'."
        );
        
        $definition = new phpucConsoleInputDefinition();
        $definition->addCommand( 'manuel', 'Hello World.' );
        $definition->addOption( 'manuel', 'p1', 'pichler', 'Hello World 1.' );
        $definition->addOption( 'manuel', 'p2', 'pichler', 'Hello World 2.' );
    }
    
    /**
     * Tests that {@link phpucConsoleInputDefinition::addOption()} fails with
     * an exception if the mandatory parameter is not of type <b>boolean</b>. 
     *
     * @return void
     */
    public function testAddOptionWithInvalidMandatoryValueFail()
    {
        $this->setExpectedException(
            'phpucErrorException',
            'The mandatory parameter must be of type boolean.'
        );
        
        $definition = new phpucConsoleInputDefinition();
        $definition->addCommand( 'manuel', 'Hello World.' );
        $definition->addOption( 
            'manuel', 'p', 'pichler', 'Hello World.', null, null, null 
        );
    }
    
    /**
     * Tests that {@link phpucConsoleInputDefinition::addOption()} fails with an
     * exception if an invalid mode value is passed in. 
     *
     * @return void
     */
    public function testAddOptionWithInvalidModeFail()
    {
        $definition = new phpucConsoleInputDefinition();
        $definition->addCommand( 'manuel', 'Hello World.' );
        $definition->addOption(
            'manuel', 'p1', 'pichler1', 'Hello World.', null, null, false, 
            phpucConsoleInputDefinition::MODE_HIDDEN
        );
        $definition->addOption(
            'manuel', 'p2', 'pichler2', 'Hello World.', null, null, false, 
            phpucConsoleInputDefinition::MODE_NORMAL
        );
        
        $this->setExpectedException(
            'phpucErrorException',
            'Invalid value for mode given.'
        );
        
        $definition->addOption(
            'manuel', 'p3', 'pichler3', 'Hello World.', null, null, false, 
            -42
        );
    }
    
    /**
     * Tests that {@link phpucConsoleInputDefinition::addOption()} adds an option
     * to the internal data structure.
     *
     * @return void
     */
    public function testAddOption()
    {
        $definition = new phpucConsoleInputDefinition();
        $definition->addCommand( 'manuel', 'Hello World.' );
        
        $this->assertFalse( $definition->hasOption( 'manuel', 'p1' ) );
        $this->assertFalse( $definition->hasOption( 'manuel', 'pichler1' ) );
        $definition->addOption(
            'manuel', 'p1', 'pichler1', 'Hello World.', null, null, false, 
            phpucConsoleInputDefinition::MODE_HIDDEN
        );
        $this->assertTrue( $definition->hasOption( 'manuel', 'p1' ) );
        $this->assertTrue( $definition->hasOption( 'manuel', 'pichler1' ) );
        
    }
}