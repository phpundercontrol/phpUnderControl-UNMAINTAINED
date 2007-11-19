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
 * Settings for the php documentor tool.
 *
 * @package   phpUnderControl
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007 Manuel Pichler. All rights reserved.
 * @license   GPL http://www.gnu.org/licenses/gpl-3.0.txt
 * @version   $Id$
 */
class pucPhpDocumentorSetting extends pucAbstractPearSetting
{
    /**
     * The ctor takes the PEAR install dir as an optional argument.
     *
     * @param string $pearInstallDir PEAR install dir.
     * @param string $outputDir      An output dir for the generated contents.
     */
    public function __construct( $pearInstallDir = null, $outputDir = null )
    {
        parent::__construct( 'phpdoc', $pearInstallDir, $outputDir );
    }

    /**
     * Generates the required output/file content.
     *
     * @return string
     */
    public function generate()
    {
        return sprintf( '
  <target name="%s">
    <exec executable="%s"
          dir="${basedir}/source"
          logerror="on">
      <arg line="-ue on -t %s/api -d ." />
    </exec>
  </target>
',
            $this->cliTool,
            $this->fileName,
            $this->outputDir
        );

        return $xml;
    }
}
