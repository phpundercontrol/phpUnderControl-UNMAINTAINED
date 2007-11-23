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
 * Main installer class.
 *
 * @package   phpUnderControl
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   $Id$
 */
class phpucPhpUnderControl
{
    /**
     * Directory with all phpUnderControl class files.
     *
     * @type string
     * @var string $installDir
     */
    private static $installDir = null;
    
    /**
     * Class to file mapping.
     *
     * @type array<string> 
     * @var array(string=>string) $autoloadFiles
     */
    private static $autoloadFiles = array(
        'phpucAbstractCommand'     =>  'Commands/AbstractCommand.php',
        'phpucExampleCommand'      =>  'Commands/ExampleCommand.php',
        'phpucInstallCommand'      =>  'Commands/InstallCommand.php',
        'phpucProjectCommand'      =>  'Commands/ProjectCommand.php',
        'phpucAbstractPearTask'    =>  'Tasks/AbstractPearTask.php',
        'phpucAbstractTask'        =>  'Tasks/AbstractTask.php',
        'phpucCruiseControlTask'   =>  'Tasks/CruiseControlTask.php',
        'phpucPhpCodeSnifferTask'  =>  'Tasks/PhpCodeSnifferTask.php',
        'phpucPhpDocumentorTask'   =>  'Tasks/PhpDocumentorTask.php',
        'phpucPhpUnitTask'         =>  'Tasks/PhpUnitTask.php',
        'phpucTaskI'               =>  'Tasks/TaskI.php',
        'phpucToolTaskI'           =>  'Tasks/ToolTaskI.php',
        'phpucConsoleArgs'         =>  'Util/ConsoleArgs.php',
        'phpucPhpUnderControl'     =>  'PhpUnderControl.php',
    );
    
    /**
     * Autoload function.
     *
     * @param string $className Unresolved class name.
     * 
     * @return void
     */
    public static function autoload( $className )
    {
        if ( isset( self::$autoloadFiles[$className] ) )
        {
            $fileName = sprintf(
                '%s/../src/%s',
                dirname( __FILE__ ),
                self::$autoloadFiles[$className]
            );
        
            include $fileName;
        }
    }
    
    /**
     * Main method for phpUnderControl
     *
     * @return void
     */
    public static function main()
    {
        self::$installDir = dirname( __FILE__ );
        
        spl_autoload_register( array( 'phpucPhpUnderControl', 'autoload' ) );
        
        $phpUnderControl = new phpucPhpUnderControl();
        $phpUnderControl->run();
    }
    
    /**
     * The used console arguments objects.
     *
     * @type phpucConsoleArgs
     * @var phpucConsoleArgs $consoleArgs
     */
    private $consoleArgs = null;
    
    /**
     * List with all tasks.
     *
     * @type array<phpucTaskI>
     * @var array(phpucTaskI) $tasks
     */
    private $tasks = array();
    
    /**
     * The ctor creates the required console arg instance.
     */
    public function __construct()
    {
        $this->consoleArgs = new phpucConsoleArgs();
    }
    
    public function run()
    {
        $this->consoleArgs->parse();
        
        $this->initTasks();
        $this->validateTasks();
        
        $mode = phpucAbstractCommand::createCommand( 
            $this->consoleArgs, 
            $this->tasks 
        );
        $mode->execute();
    }
    
    /**
     * Initializes all {@link phpucTaskI} objects.
     *
     * @return void
     */
    private function initTasks()
    {
        $args = $this->consoleArgs->arguments;
        $opts = $this->consoleArgs->options;
        
        $projectName = null;
        if ( isset( $opts['name-of-project'] ) )
        {
            $projectName = $opts['name-of-project'];
        }
        
        $this->tasks[] = new phpucCruiseControlTask( 
            $args['cc-install-dir'], $projectName
        );
        
        $pearDir = null;
        if ( isset( $opts['pear-executables-dir'] ) && trim( $opts['pear-executables-dir'] ) !== '' )
        {
            $pearDir = $opts['pear-executables-dir'];
        }
        
        if ( !isset( $opts['without-php-documentor'] ) )
        {
            $this->tasks[] = new phpucPhpDocumentorTask( $pearDir );
        }
        if ( !isset( $opts['without-code-sniffer'] ) )
        {
            $this->tasks[] = new phpucPhpCodeSnifferTask( $pearDir );
        }
        if ( !isset( $opts['without-phpunit'] ) )
        {
            $this->tasks[] = new phpucPhpUnitTask( $pearDir );
        }
    }
    
    /**
     * Ask's all tasks for valid data.
     *
     * @return void
     */
    private function validateTasks()
    {
        foreach ( $this->tasks as $task )
        {
            $task->validate();
        }
    }
}

phpucPhpUnderControl::main();