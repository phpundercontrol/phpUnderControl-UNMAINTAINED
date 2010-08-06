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
 * @author    Sebastian Marek <proofek@gmail.com>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://www.phpundercontrol.org/
 */

require_once dirname( __FILE__ ) . '/../AbstractTest.php';

/**
 * Test case for the {@link phpucSetupCcCommand} class.
 *
 * @category  QualityAssurance
 * @package   Commands
 * @author    Sebastian Marek <proofek@gmail.com>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucSetupCcCommandTest extends phpucAbstractTest
{
    /**
     * That's that init script has been copied into init scripts directory
     * and has correct permissions (must be executable).
     *
     * @return void
     * @group commands
     */
    public function testSetupCcInstallsInitScript()
    {
        $root = $this->createCruiseControlDummy();
        $initDir = PHPUC_TEST_DIR . '/init.d';

        $args = new phpucConsoleArgs(
            'setup-cc',
            array(
                'java-home' => '/usr',
                'cc-user'   => 'cruisecontrol',
                'cc-bin'    => 'cruisecontrol.sh'),
            array(
                'cc-install-dir' => $root,
                'init-dir'       => $initDir)
        );

        $command = new phpucSetupCcCommand();
        $command->setConsoleArgs( $args );
        $command->execute();

        $this->assertFileExists( "$initDir/cruisecontrol" );
        $this->assertEquals(
            '0755',
            substr( decoct( fileperms( "$initDir/cruisecontrol" ) ), 1)
        );
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
        $this->createInitScriptsDirectory();

        return PHPUC_TEST_DIR . '/CruiseControlDummy';
    }
}