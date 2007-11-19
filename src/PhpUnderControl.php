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
 * Main installer class.
 *
 * @package   phpUnderControl
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007 Manuel Pichler. All rights reserved.
 * @license   GPL http://www.gnu.org/licenses/gpl-3.0.txt
 * @version   $Id$
 */
class pucPhpUnderControl
{
    /**
     * The used console arguments objects.
     *
     * @type pucConsoleArgs
     * @var pucConsoleArgs $consoleArgs
     */
    private $consoleArgs = null;
    
    /**
     * List with all settings.
     *
     * @type array<pucSettingI>
     * @var array(pucSettingI) $settings
     */
    private $settings = array();
    
    /**
     * The ctor creates the required console arg instance.
     */
    public function __construct()
    {
        $this->consoleArgs = new pucConsoleArgs();
    }
    
    public function run()
    {
        $this->consoleArgs->parse();
        
        $this->initSettings();
        $this->validateSettings();
        
        $mode = pucAbstractMode::createMode( 
            $this->consoleArgs->mode, 
            $this->settings 
        );
        $mode->execute();
    }
    
    /**
     * Initializes all setting objects.
     *
     * @return void
     */
    private function initSettings()
    {
        $args = $this->consoleArgs->arguments;
        $opts = $this->consoleArgs->options;
        
        $exampleName = null;
        if ( isset( $opts['name-of-example'] ) )
        {
            $exampleName = $opts['name-of-example'];
        }
        
        $this->settings[] = new pucCruiseControlSetting( 
            $args['cc-install-dir'],
            $exampleName
        );
        
        $pearDir = null;
        if ( isset( $opts['pear-install-dir'] ) && trim( $opts['pear-install-dir'] ) !== '' )
        {
            $pearDir = $opts['pear-install-dir'];
        }
        $webDir = null;
        if ( isset( $opts['web-output-dir'] ) && trim( $opts['web-output-dir'] ) !== '' )
        {
            $webDir = $opts['web-output-dir'];
        }
        
        if ( !isset( $opts['without-php-documentor'] ) )
        {
            $this->settings[] = new pucPhpDocumentorSetting( $pearDir, $webDir );
        }
        if ( !isset( $opts['without-code-sniffer'] ) )
        {
            $this->settings[] = new pucPhpCodeSnifferSetting( $pearDir, $webDir );
        }
        if ( !isset( $opts['without-phpunit'] ) )
        {
            $this->settings[] = new pucPhpUnitSetting( $pearDir, $webDir );
        }
    }
    
    /**
     * Ask's all settings for valid data.
     *
     * @return void
     */
    private function validateSettings()
    {
        foreach ( $this->settings as $setting )
        {
            $setting->validate();
        }
    }
}