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
 * @package   Tasks
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

require_once dirname( __FILE__ ) . '/AbstractTaskTest.php';

/**
 * Test case for the graph install task.
 *
 * @category  QualityAssurance
 * @package   Tasks
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucGenerateGraphTaskTest extends phpucAbstractTaskTest
{
    /**
     * testValidateThrowsExceptionWhenLogDirectoryNotExists
     *
     * @return void
     * @group phpUnderControl
     * @group phpUnderControl::Tasks
     * @expectedException phpucValidateException
     */
    public function testValidateThrowsExceptionWhenLogDirectoryNotExists()
    {
        $args = $this->prepareConsoleArgs(
            array(
                'graph',
                __FILE__ . '/' . __CLASS__
            )
        );

        $task = new phpucGenerateGraphTask();
        $task->setConsoleArgs( $args );
        $task->validate();
    }

    /**
     * testValidateThrowsExceptionWhenArtifactsDirectoryNotExists
     *
     * @return void
     * @group phpUnderControl
     * @group phpUnderControl::Tasks
     * @expectedException phpucValidateException
     */
    public function testValidateThrowsExceptionWhenArtifactsDirectoryNotExists()
    {
        $args = $this->prepareConsoleArgs(
            array(
                'graph',
                dirname( __FILE__ ),
                __FILE__ . '/' . __CLASS__
            )
        );

        $task = new phpucGenerateGraphTask();
        $task->setConsoleArgs( $args );
        $task->validate();
    }

    /**
     * testValidateThrowsExceptionWhenMaxNumberIsLessThanTwo
     *
     * @return void
     * @group phpUnderControl
     * @group phpUnderControl::Tasks
     * @expectedException phpucValidateException
     */
    public function testValidateThrowsExceptionWhenMaxNumberIsLessThanTwo()
    {
        $args = $this->prepareConsoleArgs(
            array(
                'graph',
                '--max-number',
                '1',
                dirname( __FILE__ )
            )
        );

        $task = new phpucGenerateGraphTask();
        $task->setConsoleArgs( $args );
        $task->validate();
    }

    /**
     * testValidateAcceptsMaxNumberEqualToTwo
     *
     * @return void
     * @group phpUnderControl
     * @group phpUnderControl::Tasks
     */
    public function testValidateAcceptsMaxNumberEqualToTwo()
    {
        $args = $this->prepareConsoleArgs(
            array(
                'graph',
                '--max-number',
                '2',
                dirname( __FILE__ )
            )
        );

        $task = new phpucGenerateGraphTask();
        $task->setConsoleArgs( $args );
        $task->validate();
    }
}