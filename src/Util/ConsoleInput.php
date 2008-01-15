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
 * @package   Util
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

/**
 * 
 *
 * @category  QualityAssurance
 * @package   Util
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 * 
 * @property-read phpucConsoleArgs The console arguments.
 */
class phpucConsoleInput
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
     * The argument array form the command line interface.
     *
     * @type array<string>
     * @var array(string) $argv
     */
    private $argv = array();
    
    /**
     * The given command.
     *
     * @type string
     * @var string $command
     */
    private $command = null;
    
    /**
     * The given command line options.
     *
     * @type array<mixed>
     * @var array(string=>mixed) $options
     */
    private $options = array();
    
    /**
     * The given command line arguments.
     *
     * @type array<string>
     * @var array(string=>string) $arguments
     */
    private $arguments = array();
    
    /**
     * List of valid modes.
     *
     * @type array<array>
     * @var array(string=>array) $commands
     */
    private $commands = array(
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
     * List of properties read from the command line interface.
     *
     * @type array<mixed>
     * @var array(string=>mixed) $properties
     */
    private $properties = array(
        'args'  =>  null,
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
            throw new phpucConsoleException(
                'An unknown command line argument error occured.'
            );
        }
        else
        {
            throw new phpucConsoleException(
                'Please enable "register_argc_argv" for your php cli installation.'
            );
        }
    }
    
    /**
     * Parses the input command line options and arguments.
     *
     * @return boolean
     */
    public function parse()
    {
        if ( $this->hasHelpOption() === true )
        {
            $this->printHelp();
            return false;
        }
        else if ( $this->hasUsageOption() === true )
        {
            $this->printUsage();
            return false;
        }
        
        // First argument must be the mode
        if ( $this->parseCommand() === false )
        {
            $this->printUsage();
            throw new phpucConsoleException(
                'You must enter a valid installation mode as first argument.'
            );
        }
        
        $this->parseOptions();
        $this->parseArguments();
        
        $this->properties['args'] = new phpucConsoleArgs(
            $this->command,
            $this->options,
            $this->arguments
        );

        return true;
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
    private function parseCommand()
    {
        $command = array_shift( $this->argv );
        
        if ( !isset( $this->commands[$command] ) )
        {
            return false;
        }
        $this->command = $command;
        
        return true;
    }
    
    /**
     * Parses all given command line options.
     *
     * @return void
     */
    private function parseOptions()
    {
        $opts = array();
        if ( isset( $this->commands[$this->command]['options'] ) )
        {
            $opts = $this->commands[$this->command]['options'];
        }
        
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
                else if ( !isset( $opt['default'] ) )
                {
                    throw new phpucConsoleException(
                        sprintf( 
                            'The option %s is marked as mandatory and not set.', 
                            $long
                        ) 
                    );                 
                }
                
                $option = '--' . $opt['long'];
                
                array_unshift( $this->argv, $opt['default'] );
                array_unshift( $this->argv, $option );
            }
            
            // Search array index for option.
            $idx = array_search( $option, $this->argv );
            
            if ( $opt['arg'] === null )
            {
                // Mark option as set
                $this->options[$opt['long']] = true;
                // Unset option in arg array
                unset( $this->argv[$idx] );
                
                continue;
            }

            // Check for a value
            ++$idx;
            if ( !isset( $this->argv[$idx] ) 
              || strpos( $this->argv[$idx], '-' ) === 0 )
            {
                throw new phpucConsoleException(
                    sprintf( 'The option %s requires an additional value.', $option )
                );
            }
            $value = $this->argv[$idx];
            
            // Unset option and value
            unset( $this->argv[$idx - 1], $this->argv[$idx] ); 
            
            if ( is_array( $opt['arg'] ) 
              && in_array( $value, $opt['arg'] ) === false )
            {
                throw new phpucConsoleException(
                    sprintf(
                        'The value for option %s must match one of these values %s.',
                        $option,
                        '"' . implode( '", "', $opt['arg'] ) . '"'
                    )
                );
            }
            else if ( is_string( $opt['arg'] ) 
                   && preg_match( $opt['arg'], $value ) === 0 )
            {
                throw new phpucConsoleException(
                    sprintf( 
                        'The value for option %s has an invalid format.%s', $option
                    )
                );
            }
            $this->options[$opt['long']] = $value;
        }
    }
    
    /**
     * Parses all command line arguments.
     *
     * @return void
     */
    private function parseArguments()
    {
        $args = $this->commands[$this->command]['args'];
        
        foreach ( $args as $name => $arg )
        {
            $value = array_shift( $this->argv );
            if ( $value === null )
            {
                if ( $arg['mandatory'] )
                {
                    throw new phpucConsoleException(
                        sprintf( 'Missing argument <%s>.', $name )
                    );
                }
                return;
            }
            $this->arguments[$name] = $value;
        }
    }
    
    /**
     * Generates the help message for the command line tool.
     *
     * @return void
     */
    private function printHelp()
    {
        // Try to find a command
        if ( $this->parseCommand() === false )
        {
            // First print general usage.
            $this->printUsage();
            
            echo PHP_EOL;

            // Print all options and arguments
            foreach ( $this->commands as $command => $config )
            {
                // Skip hidden commands
                if ( $config['mode'] === self::MODE_HIDDEN )
                {
                    continue;
                }
                
                $this->printModeHelp( $command );
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
            $this->printModeHelp( $this->command );
        }
    }
    
    /**
     * Prints the help text for a single installer command.
     *
     * @param string $command The installer command.
     * 
     * @return void
     */
    private function printModeHelp( $command )
    {
        printf(
            'Command line options and arguments for "%s"%s',
            $command,
            PHP_EOL
        );
        
        $opts = array();
        if ( isset( $this->commands[$command]['options'] ) )
        {
            $opts = $this->commands[$command]['options'];
        }
        
        foreach ( $opts as $opt )
        {
            $tokens = $this->tokenizeHelp( $opt['help'] );
            
            printf(
                ' -% -2s --% -23s %s%s',
                $opt['short'],
                $opt['long'],
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
        
        foreach ( $this->commands[$command]['args'] as $name => $arg )
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
        $commands = '';
        foreach ( $this->commands as $command => $config )
        {
            // Skip hidden commands
            if ( $config['mode'] === self::MODE_HIDDEN )
            {
                continue;
            }
            
            $commands .= sprintf(
                '  * % -10s  %s%s',
                $command,
                $config['help'],
                PHP_EOL
            );
        }
        
        printf( 
            'Usage: phpuc.php <command> <options> <arguments>%s' .
            'For single command help type:%s' .
            '    phpuc.php <command> --help%s' . 
            'Available commands:%s%s',
            PHP_EOL,
            PHP_EOL,
            PHP_EOL,
            PHP_EOL,
            $commands
        );
    }
}