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
 * @package phpUnderControl
 */

/**
 * Utility class that handles the command line arguments for this tool.
 *
 * @package   phpUnderControl
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   $Id$
 */
class pucConsoleArgs
{
    /**
     * The argument array form the command line interface.
     *
     * @type array<string>
     * @var array(string) $argv
     */
    private $argv = array();
    
    /**
     * List of valid modes.
     *
     * @type array<array>
     * @var array(string=>array) $modes
     */
    private $modes = array(
        'install'  =>  array(
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
                    'long'       =>  'pear-install-dir',
                    'arg'        =>  true,
                    'help'       =>  'The pear install directory with the command line scripts.',
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
                    'long'       =>  'pear-install-dir',
                    'arg'        =>  true,
                    'help'       =>  'The pear install directory with the command line scripts.',
                    'mandatory'  =>  false,
                ),
                array(
                    'short'      =>  'n',
                    'long'       =>  'name-of-example',
                    'arg'        =>  true,
                    'help'       =>  'The name of the generated example project.',
                    'mandatory'  =>  false,
                ),
                array(
                    'short'      =>  'w',
                    'long'       =>  'web-output-dir',
                    'arg'        =>  true,
                    'help'       =>  'An optional web directory where the generated contents of phpunit and phpdoc will be visible.',
                    'mandatory'  =>  false,
                ),
                array(
                    'short'      =>  't',
                    'long'       =>  'type',
                    'arg'        =>  array( 'ant' ),
                    'help'       =>  'CruiseControl configuration type. Allowed is "ant" at the moment.',
                    'mandatory'  =>  true,
                )
            ),
            'args'  =>  array(
                'cc-install-dir'  =>  array(
                    'help'       =>  'The installation directory of CruiseControl.',
                    'mandatory'  =>  true
                )
            )
        )
    );
    
    /**
     * List of properties read from the command line interface.
     *
     * @type array<mixed>
     * @var array(string=>mixed) $properties
     */
    private $properties = array(
        'mode'       =>  null,
        'options'    =>  array(),
        'arguments'  =>  array()
    );
    
    /**
     * The ctor checks the current script environment.
     */
    public function __construct()
    {
        if ( isset( $GLOBALS['argv'] ) )
        {
            $this->argv = $GLOBALS['argv'];
            // Drop first arg with file name
            array_shift( $this->argv );
            
            return;
        }
        $argc_argv = strtolower( ini_get( 'register_argc_argv' ) );
        if ( $argc_argv === 'on' || $argc_argv === '1' )
        {
            echo 'An unknown command line argument error occured.' . PHP_EOL;
            exit( 1 );
        }
        else
        {
            echo 'Please enable "register_argc_argv" for your php cli installation.' . PHP_EOL;
            exit( 1 );
        }
    }
    
    /**
     * Parses the input command line options and arguments.
     *
     * @return void
     */
    public function parse()
    {
        if ( $this->hasHelpOption() === true )
        {
            $this->printHelp();
            exit( 0 );
        }
        else if ( $this->hasUsageOption() === true )
        {
            $this->printUsage();
            exit( 0 );
        }
        
        // First argument must be the mode
        if ( $this->parseMode() === false )
        {
            echo 'You must enter a valid installation mode as first argument.' . PHP_EOL;
            $this->printUsage();
            exit( 1 );
        }
        
        $this->parseOptions();
        $this->parseArguments();
    }
    
    /**
     * Magic property getter method.
     *
     * @param string $name The property name.
     * 
     * @return mixed
     * @throws OutOfRangeException If the requested property doesn't exist or
     *         is writonly.
     */
    public function __get( $name )
    {
        if ( array_key_exists( $name, $this->properties ) === true )
        {
            return $this->properties[$name];
        }
        throw new OutOfRangeException(
            sprintf( 'Unknown or writonly property $%s.', $name )
        );
    }
    
    /**
     * Checks if the help option isset in the arguments.
     *
     * @return boolean
     */
    private function hasHelpOption()
    {
        return in_array( '-h', $this->argv ) || in_array( '--help', $this->argv );
    }
    
    /**
     * Checks if the usage option isset in the arguments.
     *
     * @return boolean
     */
    private function hasUsageOption()
    {
        return in_array( '-u', $this->argv ) || in_array( '--usage', $this->argv );
    }
    
    /**
     * Parses the first argument from the command line. This must be a valid 
     * installer mode.
     *
     * @return boolean
     */
    private function parseMode()
    {
        $mode = array_shift( $this->argv );
        
        if ( !isset( $this->modes[$mode] ) )
        {
            return false;
        }
        $this->properties['mode'] = $mode;
        
        return true;
    }
    
    /**
     * Parses all given command line options.
     *
     * @return void
     */
    private function parseOptions()
    {
        $opts = $this->modes[$this->mode]['options'];
        
        foreach ( $opts as $opt )
        {
            $short = sprintf( '-%s', $opt['short'] );
            $long  = sprintf( '--%s', $opt['long'] );
            
            $option = null;
            if ( in_array( $short, $this->argv ) === true )
            {
                $option = $short;
            }
            else if ( in_array( $long, $this->argv ) === true )
            {
                $option = $long;
            }
            
            
            if ( $option === null )
            {
                if ( $opt['mandatory'] === false )
                {
                    continue;
                }
                printf( 
                    'The option %s is marked as mandatory and not set.%s', 
                    $long, 
                    PHP_EOL 
                );
                exit( 1 );
            }
            
            // Search array index for option.
            $idx = array_search( $option, $this->argv );
            
            if ( $opt['arg'] === null )
            {
                // Mark option as set
                $this->properties['options'][$opt['long']] = true;
                // Unset option in arg array
                unset( $this->argv[$idx] );
                
                continue;
            }

            // Check for a value
            ++$idx;
            if ( !isset( $this->argv[$idx] ) || strpos( $this->argv[$idx], '-' ) === 0 )
            {
                printf(
                    'The option %s requires an additional value.%s',
                    $option,
                    PHP_EOL
                );
                exit( 1 );
            }
            $value = $this->argv[$idx];
            
            // Unset option and value
            unset( $this->argv[$idx - 1], $this->argv[$idx] ); 
            
            if ( is_array( $opt['arg'] ) && in_array( $value, $opt['arg'] ) === false )
            {
                printf(
                    'The value for option %s must be match on one of these values "%s".%s',
                    $option,
                    implode( '", "', $opt['arg'] ),
                    PHP_EOL
                );
                exit( 1 );
            }
            else if ( is_string( $opt['arg'] ) && preg_match( $opt['arg'], $value ) === 0 )
            {
                printf(
                    'The value for option %s has an invalid format.%s',
                    $option,
                    PHP_EOL
                );
                exit( 1 );
            }
            $this->properties['options'][$opt['long']] = $value;
        }
    }
    
    /**
     * Parses all command line arguments.
     *
     * @return void
     */
    private function parseArguments()
    {
        $args = $this->modes[$this->mode]['args'];
        
        foreach ( $args as $name => $arg )
        {
            $value = array_shift( $this->argv );
            if ( $value === null )
            {
                if ( $arg['mandatory'] )
                {
                    printf( 'Missing argument <%s>.%s', $name, PHP_EOL );
                    exit( 1 );
                }
                return;
            }
            $this->properties['arguments'][$name] = $value;
        }
    }
    
    /**
     * Generates the help message for the command line tool.
     *
     * @return void
     */
    private function printHelp()
    {
        // Try to find a mode
        if ( $this->parseMode() === false )
        {
            // First print general usage.
            $this->printUsage();

            // Print all options and arguments
            foreach ( array_keys( $this->modes ) as $mode )
            {
                $this->printModeHelp( $mode );
            }
            
            printf(
                ' -% -2s --% -23s %s%s -% -2s --% -23s %s%s',
                'h',
                'help',
                'Print this help text.',
                PHP_EOL,
                'u',
                'usage',
                'Print a short usage example.',
                PHP_EOL
            );
        }
        else
        {
            $this->printModeHelp( $this->mode );
        }
    }
    
    /**
     * Prints the help text for a single installer mode.
     *
     * @param string $mode The installer mode.
     * 
     * @return void
     */
    private function printModeHelp( $mode )
    {
        printf(
            'Command line options and arguments for "%s"%s',
            $mode,
            PHP_EOL
        );
        
        foreach ( $this->modes[$mode]['options'] as $opts )
        {
            $tokens = $this->tokenizeHelp( $opts['help'] );
            
            printf(
                ' -% -2s --% -23s %s%s',
                $opts['short'],
                $opts['long'],
                array_shift( $tokens ),
                PHP_EOL
            );
            foreach ( $tokens as $token )
            {
                printf(
                    '                               %s%s', $token, PHP_EOL
                );
            }
        }
        
        foreach ( $this->modes[$mode]['args'] as $name => $arg )
        {
            $tokens = $this->tokenizeHelp( $arg['help'] );
            
            printf(
                ' % -29s %s%s',
                "<{$name}>",
                array_shift( $tokens ),
                PHP_EOL
            );
            foreach ( $tokens as $token )
            {
                printf(
                    '                                 %s%s', $token, PHP_EOL
                );
            }
        }
        echo PHP_EOL;
    }
    
    /**
     * Splits long help texts into smaller tokens of max 42 characters.
     * 
     * @param string $help The original help text.
     * 
     * @return array(string)
     */
    private function tokenizeHelp( $help )
    {
        $tokens = preg_split( '#(\r\n|\n|\r)#', wordwrap( $help, 44 ) );
        return array_map( 'trim', $tokens );
    }
    
    /**
     * Prints a general usage for the command line tool.
     *
     * @return void
     */
    private function printUsage()
    {
        printf( 
            'Usage: phpuc.php %s <options> <arguments>%s',
            implode( '|', array_keys( $this->modes ) ),
            PHP_EOL 
        );
    }
}