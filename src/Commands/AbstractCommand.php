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
 * @package    phpUnderControl
 * @subpackage Commands
 */

/**
 * Implementation mode of the example mode.
 *
 * @package    phpUnderControl
 * @subpackage Commands
 * @author     Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright  2007 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    $Id: InstallMode.php 1699 2007-11-23 15:18:12Z mapi $
 */
abstract class phpucAbstractCommand
{   
    protected static $baseDir = null;
    
    /**
     * Factory method for the different cli modes.
     *
     * @param phpucConsoleArgs  $args  The console arguments.
     * @param array(phpucTaskI) $tasks List of command line tasks.
     * 
     * @return phpucAbstractCommand
     */
    public static function createCommand( phpucConsoleArgs $args, array $tasks )
    {
        // Generate class name
        $className = sprintf( 'phpuc%sCommand', ucfirst( $args->mode ) );
        
        if ( class_exists( $className, true ) === false )
        {
            printf( 'Unknown mode "%s" used.%s', $args->mode, PHP_EOL );
            exit( 1 );
        }
        
        return new $className( $args, $tasks );
    }
    
    /**
     * List with all tasks.
     *
     * @type array<phpucTaskI>
     * @var array(phpucTaskI) $settings
     */
    protected $settings = array();
    
    /**
     * The console argument object.
     *
     * @type phpucConsoleArgs
     * @var phpucConsoleArgs $consoleArgs
     */
    protected $consoleArgs = null;
    
    /**
     * Protected ctor that takes the tasks and console arguments as parameters.
     * 
     * @param phpucConsoleArgs  $args  The console arguments.
     * @param array(phpucTaskI) $tasks List of command line tasks.
     */
    protected final function __construct( phpucConsoleArgs $args, array $tasks )
    {
        $this->consoleArgs = $args;
        $this->settings    = $tasks;
        
        self::$baseDir = realpath( dirname( __FILE__ ) . '/../..' );
    }
    
    /**
     * Executes this mode task.
     *
     * @return void
     */
    public abstract function execute();
    
    /**
     * Returns the cruise control setting object.
     *
     * @return pucCruiseControlSetting
     */
    protected function getCCSetting()
    {
        foreach ( $this->settings as $setting )
        {
            if ( $setting instanceof pucCruiseControlSetting )
            {
                return $setting;
            }
        }
        // This should never happen.
        throw new ErrorException( 'No CruiseControl setting defined.' );
    }
    
    /**
     * Returns all as tool marked task objects.
     *
     * @return array(phpucToolTaskI)
     */
    protected function getToolTasks()
    {
        $tasks = array();
        foreach ( $this->settings as $task )
        {
            if ( $task instanceof phpucToolTaskI )
            {
                $tasks[] = $task;
            }
        }
        return $tasks;
    }
}