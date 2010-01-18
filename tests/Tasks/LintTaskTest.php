<?php
/**
 * This file is part of phpUnderControl.
 *
 * Copyright (c) 2007-2010, Manuel Pichler <mapi@phpundercontrol.org>.
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
 * @package   Tasks
 * @author    Sebastian Marek <proofek@gmail.com>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

require_once dirname( __FILE__ ) . '/AbstractPearTaskTest.php';

/**
 * Test case for the php lint task.
 *
 * @package   Tasks
 * @author    Sebastian Marek <proofek@gmail.com>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucLintTaskTest extends phpucAbstractPearTaskTest
{
    /**
     * Tests that the execute method adds a correct build file target.
     *
     * @covers phpucLintTask::execute
     *
     * @return void
     */
    public function testLintExecuteBuildFileModifications()
    {
        $lintTask = new phpucLintTask();
        $lintTask->setConsoleArgs( $this->args );
        $lintTask->execute();

        $sxml = simplexml_load_file( $this->projectDir . '/build.xml' );

        $build = $sxml->xpath( '/project/target[@name="build"]' );
        $lint = $sxml->xpath( '/project/target[@name="lint"]' );

        $this->assertEquals( 1, count( $lintTask ) );
        $this->assertEquals( 'lint', (string) $build[0]['depends'] );
        $this->assertEquals( 'lint', (string) $lint[0]['name'] );

        $apply = $lint[0]->apply;

        $this->assertType( 'SimpleXMLElement', $apply );
        $this->assertEquals( 'php', (string) $apply['executable'] );
        $this->assertEquals( 'on',  (string) $apply['failonerror'] );
        $this->assertEquals( 'on',  (string) $apply['logerror'] );
        $this->assertEquals( '${basedir}/source', (string) $apply['dir'] );
        $this->assertType( 'SimpleXMLElement', $apply->arg );
        $this->assertEquals( '-l', (string) $apply->arg['line'] );

        $fileset = $apply->fileset;

        $this->assertEquals( '${basedir}/source', (string) $fileset['dir'] );

        $include = $fileset->include;

        $this->assertEquals( '**/*.php', (string) $include['name'] );
    }
}
