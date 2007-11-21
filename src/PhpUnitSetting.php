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
 * Settings for the php unit tool.
 *
 * @package   phpUnderControl
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   $Id$
 * 
 * @property-read boolean $metrics  Enable metrics support?
 * @property-read boolean $coverage Enable coverage support?
 */
class pucPhpUnitSetting extends pucAbstractPearSetting
{   
    /**
     * Minimum code sniffer version.
     */
    const PHP_UNIT_VERSION = '3.2.0RC2';
    
    /**
     * The ctor takes the PEAR install dir as an optional argument.
     * 
     * @param string $pearInstallDir PEAR install dir.
     * @param string $outputDir      An output dir for the generated contents.
     */
    public function __construct( $pearInstallDir = null, $outputDir = null )
    {
        parent::__construct( 'phpunit', $pearInstallDir, $outputDir );
        
        $this->properties['metrics']  = true;
        $this->properties['coverage'] = true;
    }
    
    /**
     * Generates the required output/file content.
     *
     * @return string
     */
    public function generate()
    {
        $metrics = '';
        if ( $this->metrics === true )
        {
            $metrics = '--log-metrics ${basedir}/build/logs/phpunit.metrics.xml';
        }
        $coverage = '';
        if ( $this->coverage === true )
        {
            $coverage = '--coverage-xml  ${basedir}/build/logs/phpunit.coverage.xml
                         --coverage-html ${basedir}/build/coverage';
        }
        $output = '';
        if ( $this->outputDir !== null )
        {
             
        }
        
        $xml = sprintf( '
  <target name="%s">
    <exec executable="%s" dir="${basedir}/source/tests" failonerror="true">
      <arg line="--log-xml ${basedir}/build/logs/phpunit.xml
                 --log-pmd ${basedir}/build/logs/phpunit.pmd.xml
                 %s
                 %s
                 %s
                 PhpUnderControl_Example_MathTest MathTest.php" />
    </exec>
  </target>
',
            $this->cliTool,
            $this->fileName,
            $metrics,
            $coverage,
            $output
        );
        
        return $xml;
    }
    
    /**
     * Validates the existing code sniffer version.
     *
     * @return void
     */
    protected function doValidate()
    {
        ob_start();
        system( "{$this->fileName} --version" );
        $retval = ob_get_contents();
        ob_end_clean();

        if ( preg_match( '/\s+([0-9\.]+(RC[0-9])?)/', $retval, $match ) === 0 )
        {
            echo 'WARNING: Cannot identify PHPUnit version.' . PHP_EOL;
            // Assume valid version
            $version = self::PHP_UNIT_VERSION;
        }
        else
        {
            $version = $match[1];
        }
        
        // Check version and inform user
        if ( version_compare( $version, self::PHP_UNIT_VERSION ) < 0 )
        {
            printf(
                'NOTICE: The identified version %s doesn\'t support metrics.%s' .
                'You may switch to PHPUnit %s for cooler features.%s',
                $version,
                PHP_EOL,
                self::PHP_UNIT_VERSION,
                PHP_EOL
            );
            $this->properties['metrics'] = false;
        }

        // Check xdebug installation
        if ( extension_loaded( 'xdebug' ) === false )
        {
            printf(
                'NOTICE: The xdebug extension is not installed. For coverage%s' .
                'you must install xdebug with the following command:%s' .
                '  pecl install xdebug%s',
                PHP_EOL,
                PHP_EOL,
                PHP_EOL
            );
            $this->properties['coverage'] = false;
        }
    }
}
