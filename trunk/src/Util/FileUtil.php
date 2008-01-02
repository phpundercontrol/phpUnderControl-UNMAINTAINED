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

/**
 * File system utility class.
 *
 * @package   Util
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
final class phpucFileUtil
{
    /**
     * Indicates any windows operations system.
     */
    const OS_WINDOWS = 0;
    
    /**
     * Indicates any unix based operation system.
     */
    const OS_UNIX = 1;
    
    /**
     * List of valid operation systems.
     *
     * @type array<integer>
     * @var array(integer) $validOS
     */
    private static $validOS = array( self::OS_UNIX, self::OS_WINDOWS );
    
    /**
     * List of known windows executable extensions.
     *
     * @type array<string>
     * @var array(string) $windowsExts
     */
    private static $windowsExts = array( 'exe', 'cmd', 'bat' );
    
    /**
     * List of environment paths.
     *
     * @type array<string>
     * @var array(string) $paths
     */
    private static $paths = null;
    
    /**
     * The used operation system.
     * 
     * This property was primary introduced for class testing. 
     *
     * @type integer
     * @var integer $os
     */
    private static $os = null;
    
    /**
     * Returns the current operation system. If no operation system is configured
     * this method uses the PHP constant <b>PHP_OS</b> for detection.
     *
     * @return integer
     */
    public static function getOS()
    {
        if ( self::$os === null )
        {
            if ( stripos( PHP_OS, 'win' ) === false )
            {
                self::$os = self::OS_UNIX;
            }
            else
            {
                self::$os = self::OS_WINDOWS;
            }
        }
        return self::$os;
    }
    
    /**
     * Sets the current operation system.  The method only exist's for testing.
     *
     * @param integer $os The current system os.
     * 
     * @return void
     * @throws InvalidArgumentException If the given $os property is not a valid
     *         operation system, known by this class.
     */
    public static function setOS( $os )
    {
        if ( in_array( $os, self::$validOS, true ) === false )
        {
            throw new InvalidArgumentException(
                sprintf( 'Invalid operation system type %d.', $os )
            );
        }
        self::$os = $os;
    }
    
    /**
     * Returns an <b>array</b> with all configured system paths. If the class 
     * internal property isn't set, this method uses the environment variable 
     * 'PATH' and the PHP constant <b>PATH_SEPARATOR</b> is used.
     *
     * @return array(string)
     */
    public static function getPaths()
    {
        if ( self::$paths === null )
        {
            self::$paths = array_unique(
                explode( PATH_SEPARATOR, getenv( 'PATH' ) )
            );
        }
        return self::$paths;
    }
    
    /**
     * Allows to set some custom paths. This method is only need intended for
     * testing.
     *
     * @param array $paths List of environment paths.
     * 
     * @return void
     */
    public static function setPaths( array $paths )
    {
        self::$paths = $paths;
    }
    
    /**
     * Tries to find the full path for the given <b>$executable</b>. 
     * 
     * <b>$executable</b> should contain the unix file name with out any
     * file extension because for windows it tries to append some default
     * extensions.  
     *
     * @param string $executable The pure executable name without an extension.
     * 
     * @return string The executable path.
     * @throws phpucErrorException If the given executable doesn't exist in any
     *         of the configured paths.
     */
    public static function findExecutable( $executable )
    {
        $path = null;
        if ( self::getOS() === self::OS_UNIX )
        {
            $path = self::findUnixExecutable( $executable );
        }
        else
        {
            $path = self::findWindowsExecutable( $executable );
        }
        return $path;
    }
    
    /**
     * Tries to find the given executable on an unix system.
     *
     * @param string $executable The pure executable name without an extension.
     * 
     * @return string The executable path.
     * @throws phpucErrorException If the given executable doesn't exist in any
     *         of the configured paths.
     */
    private static function findUnixExecutable( $executable )
    {
        foreach ( self::getPaths() as $path )
        {
            $fullPath = "{$path}/{$executable}";
            
            if ( file_exists( $fullPath ) && is_executable( $fullPath ) )
            {
                return $executable;
            }
        }
        throw new phpucErrorException(
            sprintf(
                'Cannot find the executable "%s" in your environment', $executable
            )
        );
    }
    
    /**
     * Tries to find the given executable on a windows system. This means it 
     * appends all known executable file extensions to the given name and this 
     * method skips the "is_executable" check.
     *
     * @param string $executable The pure executable name without an extension.
     * 
     * @return string The executable path.
     * @throws phpucErrorException If the given executable doesn't exist in any
     *         of the configured paths.
     */
    private static function findWindowsExecutable( $executable )
    {
        foreach ( self::getPaths() as $path )
        {
            foreach ( self::$windowsExts as $ext )
            {
                $fullPath = "{$path}/{$executable}.{$ext}";
            
                if ( file_exists( $fullPath ) )
                {
                    return $executable;
                }
            }
        }
        throw new phpucErrorException(
            sprintf(
                'Cannot find the executable "%s" in your environment', $executable
            )
        );
    }
}