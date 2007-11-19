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
 * Settings for the php code sniffer tool.
 *
 * @package   phpUnderControl
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007 Manuel Pichler. All rights reserved.
 * @license   GPL http://www.gnu.org/licenses/gpl-3.0.txt
 * @version   $Id$
 */
class pucPhpCodeSnifferSetting extends pucAbstractPearSetting
{   
    /**
     * Minimum code sniffer version.
     */
    const CODE_SNIFFER_VERSION = '1.0.0RC2';
    
    /**
     * The ctor takes the PEAR install dir as an optional argument.
     * 
     * @param string $pearInstallDir PEAR install dir.
     * @param string $outputDir      An output dir for the generated contents.
     */
    public function __construct( $pearInstallDir = null, $outputDir = null )
    {
        parent::__construct( 'phpcs', $pearInstallDir, $outputDir );
    }
    
    /**
     * Generates the required output/file content.
     *
     * @return string
     */
    public function generate()
    {
        return sprintf( '
  <target name="phpcs">
    <exec executable="%s"
          output="${basedir}/build/logs/checkstyle.xml"
          dir="${basedir}">
      <arg line="--report=checkstyle --standard=PEAR source" />
    </exec>
  </target>
',
            $this->fileName
        );
    }
    
    /**
     * Validates the existing code sniffer version.
     *
     * @return void
     */
    protected function doValidate()
    {
        $retval = exec( "{$this->fileName} --version" );
        
        if ( preg_match( '/version\s+([0-9\.]+(RC[0-9])?)/', $retval, $match ) === 0 )
        {
            echo 'WARNING: Cannot identify PHP_CodeSniffer version.' . PHP_EOL;
            // Assume valid version
            $version = self::CODE_SNIFFER_VERSION;
        }
        else
        {
            $version = $match[1];
        }
        
        if ( version_compare( $version, self::CODE_SNIFFER_VERSION ) < 0 )
        {
            printf( 
                'PHP_CodeSniffer version %s or higher required. Given version is "%s".%s',
                self::CODE_SNIFFER_VERSION,
                $version,
                PHP_EOL
            );
            exit( 1 );
        }
    }
}