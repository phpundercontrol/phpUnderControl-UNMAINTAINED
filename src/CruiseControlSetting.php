<?php
/**
 * This file is part of phpUnderControl.
 *
 * phpUnderControl is free software; you can redistribute it and/or 
 * modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * phpUnderControl is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with phpUnderControl; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * 
 * @package phpUnderControl
 */

/**
 * Settings for the cruise control directory.
 *
 * @package   phpUnderControl
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007 Manuel Pichler. All rights reserved.
 * @license   GPL http://www.gnu.org/licenses/gpl-3.0.txt
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