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
 * Abstract base class for the PEAR based options.
 *
 * @package   phpUnderControl
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007 Manuel Pichler. All rights reserved.
 * @license   GPL http://www.gnu.org/licenses/gpl-3.0.txt
 * @version   $Id$
 * 
 * @property      string $cliTool        The PEAR cli command line tool.
 * @property      string $pearInstallDir An optional PEAR install directory.
 * @property      string $outputDir      An optional output directory.
 * @property-read string $fileName       The full command file name.
 */
abstract class pucAbstractPearSetting extends pucAbstractSetting
{
    /**
     * The ctor takes the cli script name as argument and the PEAR install dir 
     * as an optional argument.
     *
     * @param string $cliTool        The PEAR cli tool.
     * @param string $pearInstallDir PEAR install dir.
     * @param string $outputDir      An output dir for the generated contents.
     */
    public function __construct( $cliTool, $pearInstallDir = null, $outputDir = null )
    {
        $this->properties['cliTool']        = null;
        $this->properties['outputDir']      = null;
        $this->properties['pearInstallDir'] = null;
        
        $this->cliTool        = $cliTool;
        $this->outputDir      = $outputDir;
        $this->pearInstallDir = $pearInstallDir;
    }
    
    /**
     * Does the primary validation that the command line tool exists. If the
     * tool exists this method passes the request to the internal template 
     * method {@link doValidate()}.
     *
     * @return void
     */
    public final function validate()
    {
        // Get possible or configured pear path
        if ( $this->pearInstallDir === null )
        {
            $paths = explode( PATH_SEPARATOR, getenv( 'PATH' ) );
        }
        else
        {
            $paths = array( $this->pearInstallDir );
        }
        $paths = array_unique( $paths );

        foreach ( $paths as $path )
        {
            $fileName = sprintf( '%s/%s', $path, $this->cliTool );
            
            if ( file_exists( $fileName ) === false )
            {
                continue;
            }
            if ( is_executable( $fileName ) === false )
            {
                continue;
            }
            $this->properties['fileName'] = $fileName;
            break;
        }
        if ( $this->fileName === null )
        {
            printf(
                'Missing command line tool "%s". Please check your PATH settings.%s',
                $this->cliTool,
                PHP_EOL
            );
            exit( 1 );
        }
        else if ( $this->pearInstallDir === null )
        {
            $dir = dirname( $this->fileName );
            if ( strpos( getenv( 'PATH' ), $dir ) !== false )
            {
                $this->properties['fileName'] = $this->cliTool;
            }
        }

        // Check output directory
        if ( $this->outputDir !== null && is_dir( $this->outputDir ) === false )
        {
            printf(
                'The output directory "%s" doesn\'t exist.%s',
                $this->outputDir,
                PHP_EOL
            );
            exit( 1 );
        }
        
        $this->doValidate();
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
            case 'cliTool':
                $this->properties[$name] = $value;
                break;
                
            case 'outputDir':
                if ( trim( $value ) === '' )
                {
                    $value = sys_get_temp_dir() . '/php-under-control';
                    if ( file_exists( $value ) === false )
                    {
                        mkdir( $value );
                    }
                }
                $regex = sprintf( '#%s+$#', DIRECTORY_SEPARATOR );
                $this->properties[$name] = preg_replace( $regex, '', $value );
                break;
                
            case 'pearInstallDir':
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
    
    /**
     * Template validate method for additional checks.
     *
     * @return void
     */
    protected function doValidate()
    {
        // Nothing todo hear
    }
}