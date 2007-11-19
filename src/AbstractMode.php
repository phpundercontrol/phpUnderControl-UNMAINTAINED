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
 * Abstract base class for all modes.
 *
 * @package   phpUnderControl
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007 Manuel Pichler. All rights reserved.
 * @license   GPL http://www.gnu.org/licenses/gpl-3.0.txt
 * @version   $Id$
 */
abstract class pucAbstractMode
{
    /**
     * Factory method for the different cli modes.
     *
     * @param string             $mode     The mode identifier.
     * @param array(pucSettingI) $settings The specified settings.
     * 
     * @return pucAbstractMode
     */
    public static function createMode( $mode, array $settings )
    {
        // Generate class name
        $className = sprintf( 'puc%sMode', ucfirst( $mode ) );
        
        if ( class_exists( $className, true ) === false )
        {
            printf( 'Unknown mode "%s" used.%s', $mode, PHP_EOL );
            exit( 1 );
        }
        
        return new $className( $settings );
    }
    
    /**
     * List with all settings.
     *
     * @type array<pucSettingI>
     * @var array(pucSettingI) $settings
     */
    protected $settings = array();
    
    /**
     * Protected ctor that takes the settings as argument.
     * 
     * @param array(pucSettingI) $settings List of command line settings.
     */
    protected final function __construct( array $settings )
    {
        $this->settings = $settings;
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
     * Returns all setting objects for a single tool
     *
     * @return array(pucAbstractPearSetting)
     */
    protected function getToolSettings()
    {
        $settings = array();
        foreach ( $this->settings as $setting )
        {
            if ( $setting instanceof pucAbstractPearSetting )
            {
                $settings[] = $setting;
            }
        }
        return $settings;
    }
}