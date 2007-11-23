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
        if ( isset( $opts['pear-executables-dir'] ) && trim( $opts['pear-executables-dir'] ) !== '' )
        {
            $pearDir = $opts['pear-executables-dir'];
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