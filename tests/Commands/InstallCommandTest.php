<?php
/**
 * This file is part of phpUnderControl.
 *
 * PHP Version 5.2.0
 *
 * Copyright (c) 2007-2010, Manuel Pichler <mapi@manuel-pichler.de>.
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
 * @category  QualityAssurance
 * @package   Commands
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

require_once dirname( __FILE__ ) . '/../AbstractTest.php';

/**
 * Test case for the {@link phpucInstallCommand} class.
 *
 * @category  QualityAssurance
 * @package   Commands
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucInstallCommandTest extends phpucAbstractTest
{
    /**
     * Tests that the install command applies the new images introduced with
     * ticket 836 to an existing CruiseControl installation.
     *
     * @return void
     * @group commands
     */
    public function testInstallCommandCopiesExpectedImageFilesTicket836()
    {
        $root = $this->createCruiseControlDummy();
        $args = new phpucConsoleArgs(
            'install',
            array(),
            array( 'cc-install-dir' => $root )
        );

        $command = new phpucInstallCommand();
        $command->setConsoleArgs( $args );
        $command->execute();

        $directory = $this->getCruiseControlDummyDirectory( 'images/php-under-control/' );

        $this->assertFileExists( $directory . 'collapsed.png' );
        $this->assertFileExists( $directory . 'expanded.png' );
    }

    /**
     * Root directory for a CruiseControl dummy.
     */
    const CRUISE_CONTROL_ROOT = 'CruiseControlDummy/webapps/cruisecontrol/';

    /**
     * Creates a dummy CruiseControl directory structure and returns the root
     * directory of that installation.
     *
     * @return string
     */
    protected function createCruiseControlDummy()
    {
        $this->createTestDirectories(
            array(
                self::CRUISE_CONTROL_ROOT . 'WEB-INF/lib',
                self::CRUISE_CONTROL_ROOT . 'css',
                self::CRUISE_CONTROL_ROOT . 'images',
                self::CRUISE_CONTROL_ROOT . 'js',
                self::CRUISE_CONTROL_ROOT . 'xsl',
            )
        );

        $this->createTestFile( self::CRUISE_CONTROL_ROOT . 'buildresults.jsp' );
        $this->createTestFile( self::CRUISE_CONTROL_ROOT . 'index.jsp' );
        $this->createTestFile( self::CRUISE_CONTROL_ROOT . 'main.jsp' );
        $this->createTestFile( self::CRUISE_CONTROL_ROOT . 'metrics.jsp' );

        $this->createTestFile( self::CRUISE_CONTROL_ROOT . 'xsl/buildresults.xsl' );
        $this->createTestFile( self::CRUISE_CONTROL_ROOT . 'xsl/errors.xsl' );
        $this->createTestFile( self::CRUISE_CONTROL_ROOT . 'xsl/header.xsl' );
        $this->createTestFile( self::CRUISE_CONTROL_ROOT . 'xsl/modifications.xsl' );

        return PHPUC_TEST_DIR . '/CruiseControlDummy';
    }

    /**
     * Returns the full qualified path for a CruiseControl dummy installation.
     *
     * @param string $localPath The local directory path.
     *
     * @return string
     */
    protected function getCruiseControlDummyDirectory( $localPath )
    {
        return sprintf(
            '%s/%s/%s',
            PHPUC_TEST_DIR,
            self::CRUISE_CONTROL_ROOT,
            $localPath
        );
    }
}