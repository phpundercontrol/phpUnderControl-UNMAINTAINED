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
 * Abstract base class for all modes.
 *
 * @package   phpUnderControl
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
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