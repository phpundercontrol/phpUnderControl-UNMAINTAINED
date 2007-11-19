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
 * Settings for the cruise control directory.
 *
 * @package   phpUnderControl
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   $Id$
 * 
 * @property string $ccInstallDir The cruise control installation directory.
 * @property string $exampleName  The name of the example project.
 */
class pucCruiseControlSetting extends pucAbstractSetting
{
    /**
     * The ctor takes the given cruise control install dir as argument.
     *
     * @param string $ccInstallDir The cc install dir.
     * @param string $exampleName  The name of the example project.
     */
    public function __construct( $ccInstallDir, $exampleName = null )
    {
        $this->properties['ccInstallDir'] = null;
        $this->properties['exampleName']  = null;
        
        $this->ccInstallDir = $ccInstallDir;
        $this->exampleName  = $exampleName;
    }
    
    /**
     * Validates the required constrains.
     *
     * @return void
     */
    public function validate()
    {
        // Check for a valid directory.
        if ( is_dir( $this->ccInstallDir ) === false )
        {
            printf(
                'The specified CruiseControl directory "%s" doesn\'t exist.%s',
                $this->ccInstallDir,
                PHP_EOL
            );
            exit( 1 );
        }
        // List of required sub directories.
        $subdirs = array(
            '/projects',
            '/webapps/cruisecontrol',
            '/webapps/cruisecontrol/css',
            '/webapps/cruisecontrol/xsl',
        );

        foreach ( $subdirs as $subdir )
        {
            // Check for a valid directory.
            if ( is_dir( $this->ccInstallDir . $subdir ) === false )
            {
                printf(
                    'Missing required CruiseControl sub directory "%s".%s',
                    $subdir,
                    PHP_EOL
                );
                exit( 1 );
            }            
        }
    }
    
    /**
     * Generates the required output/file content.
     *
     * @return string
     */
    public function generate()
    {
        return '';
    }
    
    /**
     * Magic property setter method.
     *
     * @param string $name  The property name.
     * @param mixed  $value The property value.
     * 
     * @return void
     * @throws OutOfRangeException If the property doesn't exist or is readonly.
     */
    public function __set( $name, $value )
    {
        switch ( $name )
        {
            case 'exampleName':
                if ( trim( $value) === '' )
                {
                    $value = 'php-under-control';
                }
                $this->properties['exampleName'] = $value;
                break;
                
            case 'ccInstallDir':
                if ( trim( $value) === '' )
                {
                    $this->properties[$name] = null;
                }
                else
                {
                    $regex = sprintf( '#%s+$#', DIRECTORY_SEPARATOR );
                    $this->properties[$name] = preg_replace( $regex, '', $value );
                }
                break;
                
            default:
                throw new OutOfRangeException(
                    sprintf( 'Unknown or readonly property $%s.', $name )
                );
                break;
        }
    }
}