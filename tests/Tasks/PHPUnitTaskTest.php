<?php
/**
 * This file is part of phpUnderControl.
 *
 * Copyright (c) 2007-2008, Manuel Pichler <mapi@phpundercontrol.org>.
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
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

require_once dirname( __FILE__ ) . '/AbstractPearTaskTest.php';

/**
 * Test case for the php code sniffer task.
 * 
 * @package   Tasks
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucPHPUnitTaskTest extends phpucAbstractPearTaskTest
{
    /**
     * Content for a fake phpunit bin that works.
     *
     * @type string
     * @var string $validBin
     */
    protected $validBin = "#!/usr/bin/env php\n<?php echo 'version 3.2.0';?>";
    
    /**
     * Content for a fake phpunit bin that doesn't work.
     *
     * @type string
     * @var string $invalidBin
     */
    protected $invalidBin = "#!/usr/bin/env php\n<?php echo 'version 3.1.9';?>";
    
    /**
     * Sets the required binary contents.
     * 
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
        
        if ( stripos( PHP_OS, 'WIN' ) !== false )
        {
            $this->validBin   = "@echo off\n\recho version 3.2.0";
            $this->invalidBin = "@echo off\n\recho version 3.1.9";
        }
    }
    
    /**
     * Tests validate with the required phpunit version. 
     *
     * @return void
     */
    public function testPHPUnitVersionValidate()
    {
        $this->createExecutable( 'phpunit', $this->validBin );
        $phpunit = new phpucPhpUnitTask( $this->args );
        $phpunit->validate();
    }
    
    /**
     * Tests that the validate method fails for an unsupported phpunit version.
     *
     * @return void
     */
    public function testPHPUnitVersionValidateWithInvalidVersion()
    {
        $this->createExecutable( 'phpunit', $this->invalidBin );
        $phpunit = new phpucPhpCodeSnifferTask( $this->args );
        try
        {
            $phpunit->validate();
            $this->fail( 'phpucValidateException expected.' );
        }
        catch ( phpucValidateException $e ) {}
    }
}