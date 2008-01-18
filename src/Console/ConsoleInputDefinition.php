<?php
/**
 * This file is part of phpUnderControl.
 * 
 * PHP Version 5.2.4
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
 * @category  QualityAssurance
 * @package   Console
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

/**
 * Collection with all available commands and options.
 *
 * @category  QualityAssurance
 * @package   Console
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucConsoleInputDefinition implements ArrayAccess, IteratorAggregate
{
    /**
     * Marks a normal command or option that shows up in the cli help.
     */
    const MODE_NORMAL = 0;
    
    /**
     * Marks a hidden command or option. This means a command is not shown in 
     * the cli help.
     */
    const MODE_HIDDEN = 1;
    
    /**
     * List of valid modes.
     *
     * @type array<array>
     * @var array(string=>array) $definition
     */
    private $definition = array(
        'install'  =>  array(
            'mode'  =>  self::MODE_NORMAL,
            'help'  =>  'Installs the CruiseControl patches.',
            'options'  =>  array(
                array(
                    'short'      =>  'p',
                    'long'       =>  'pear-executables-dir',
                    'arg'        =>  true,
                    'help'       =>  'The pear directory with cli scripts.',
                    'mandatory'  =>  false,
                )
            ),
            'args'  =>  array(
                'cc-install-dir'  =>  array(
                    'help'       =>  'The installation directory of CruiseControl.',
                    'mandatory'  =>  true
                )
            )
        ),
        'example'  =>  array(
            'mode'  =>  self::MODE_NORMAL,
            'help'  =>  'Creates a small example project.',
            'options'  =>  array(
                array(
                    'short'      =>  'c',
                    'long'       =>  'without-code-sniffer',
                    'arg'        =>  null,
                    'help'       =>  'Disable PHP CodeSniffer support.',
                    'mandatory'  =>  false,
                ),
                array(
                    'short'      =>  'u',
                    'long'       =>  'without-phpunit',
                    'arg'        =>  null,
                    'help'       =>  'Disable PHPUnit support.',
                    'mandatory'  =>  false,
                ),
                array(
                    'short'      =>  'd',
                    'long'       =>  'without-php-documentor',
                    'arg'        =>  null,
                    'help'       =>  'Disable phpDocumentor support.',
                    'mandatory'  =>  false,
                ),
                array(
                    'short'      =>  'p',
                    'long'       =>  'pear-executables-dir',
                    'arg'        =>  true,
                    'help'       =>  'The pear directory with cli scripts.',
                    'mandatory'  =>  false,
                ),
                array(
                    'short'      =>  'n',
                    'long'       =>  'project-name',
                    'arg'        =>  true,
                    'help'       =>  'The name of the generated project.',
                    'default'    =>  'php-under-control',
                    'mandatory'  =>  true,
                ),
                array(
                    'short'      =>  'i',
                    'long'       =>  'schedule-interval',
                    'arg'        =>  true,
                    'help'       =>  'Schedule interval.',
                    'default'    =>  300,
                    'mandatory'  =>  true,
                ),
                array(
                    'short'      =>  's',
                    'long'       =>  'source-dir',
                    'arg'        =>  true,
                    'help'       =>  'The source directory in the project.',
                    'default'    =>  '.',
                    'mandatory'  =>  true,
                ),
                array(
                    'short'      =>  't',
                    'long'       =>  'test-dir',
                    'arg'        =>  true,
                    'help'       =>  'The test directory in the project.',
                    'default'    =>  'tests',
                    'mandatory'  =>  true,
                ),
                array(
                    'short'      =>  'tc',
                    'long'       =>  'test-case',
                    'arg'        =>  true,
                    'help'       =>  'Name of the test case class.',
                    'default'    =>  'PhpUnderControl_Example_MathTest',
                    'mandatory'  =>  true,
                ),
                array(
                    'short'      =>  'tf',
                    'long'       =>  'test-file',
                    'arg'        =>  true,
                    'help'       =>  'Name of the test case file.',
                    'default'    =>  'MathTest.php',
                    'mandatory'  =>  true,
                ),
                array(
                    'short'      =>  'g',
                    'long'       =>  'coding-guideline',
                    'arg'        =>  true,
                    'help'       =>  'The used PHP_CodeSniffer coding guideline.',
                    'default'    =>  'PEAR',
                    'mandatory'  =>  true,
                ),
                array(
                    'short'      =>  'b',
                    'long'       =>  'build-tool',
                    'arg'        =>  array( 'ant' ),
                    'help'       =>  'CruiseControl build system type type.',
                    'default'    =>  'ant',
                    'mandatory'  =>  true,
                )
            ),
            'args'  =>  array(
                'cc-install-dir'  =>  array(
                    'help'       =>  'The installation directory of CruiseControl.',
                    'mandatory'  =>  true
                )
            )
        ),
        'graph'  =>  array(
            'help'  =>  'Generates the metric graphs with ezcGraph',
            'mode'  =>  self::MODE_HIDDEN,
            'args'  =>  array(
                'project-log-dir'  =>  array(
                    'help'       =>  'The project log directory',
                    'mandatory'  =>  true
                )
            )
        ),
    );
    
    /**
     * Returns an iterator with all registered cli commands.
     *
     * @return Iterator
     */
    public function getIterator()
    {
        return new ArrayIterator( $this->definition );
    }
    
    /**
     * Array access method for isset.
     *
     * @param string $name The command name to look up.
     * 
     * @return boolean
     */
    public function offsetExists( $name )
    {
        return ( isset( $this->definition[$name] ) );
    }
    
    /**
     * Returns the command definition for the given name.
     * 
     * If no command for the given <b>$name</b> exists, this method will throw
     * an <b>OutOfRangeException</b>.
     *
     * @param string $name The name of the requested command.
     * 
     * @return array
     * @throws OutOfRangeException If the requested command doesn't exist.
     * @todo TODO: Change to a an instance of phpucConsoleCommandDefintion
     */
    public function offsetGet( $name )
    {
        if ( $this->offsetExists( $name ) )
        {
            return $this->definition[$name];
        }
        throw new OutOfRangeException( "Unknown index '{$name}'." );
    }
    
    /**
     * Adds a new command definition.
     *
     * @param string $name  The command name.
     * @param array  $value The command array.
     * 
     * @return void
     * @throws InvalidArgumentException If the $value is not an array.
     * @todo TODO: Change to a an instance of phpucConsoleCommandDefintion
     */
    public function offsetSet( $name, $value )
    {
        if ( !is_array( $value ) )
        {
            throw new InvalidArgumentException( 
                'A new definition must be an array.' 
            );
        }
        $this->definition[$name] = $value;
    }
    
    /**
     * Does nothing here!?!?
     *
     * @param string $name The command name.
     * 
     * @return void
     */
    public function offsetUnset( $name )
    {
        // Nothing todo here
    }
}