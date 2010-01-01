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
 * @package   Stylesheet
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

require_once dirname( __FILE__ ) . '/../AbstractTest.php';

/**
 * Test case for the XSL Stylesheet files.
 *
 * @category  QualityAssurance
 * @package   Stylesheet
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucStylesheetHeaderTest extends phpucAbstractTest
{
    /**
     * The relative xs l directory path.
     */
    const WEBAPP_XSL_DIR = '/webapps/cruisecontrol/xsl/';

    /**
     * The xsltproc binary name.
     */
    const XSLTPROC_BINARY = 'xsltproc';

    /**
     * Tests that the header stylesheet does not reprints the complete log file
     * content. This issue occured with some CC versions because the corresponding
     * template does not include a default template for <b>/</b>.
     *
     * @return void
     * @group stylesheet
     */
    public function testHeaderDoesNotContainCompleteLogFileContentXsltProcessor()
    { 
        $xsl = $this->createXslStylesheet( 'header.xsl' );
        $xml = $this->createCruiseControlLog();

        $proc = new XSLTProcessor();
        $proc->importStylesheet( $xsl );

        $this->assertSame(
            $this->loadExpectedResult(),
            trim( $proc->transformToXml( $xml ) )
        );
    }

    /**
     * Tests that the header stylesheet does not reprints the complete log file
     * content. This issue occured with some CC versions because the corresponding
     * template does not include a default template for <b>/</b>.
     *
     * @return void
     * @group stylesheet
     */
    public function testHeaderDoesNotContainCompleteLogFileContentXsltproc()
    {
        $this->markTestSkippedWhenBinaryNotExists( self::XSLTPROC_BINARY );

        $result = shell_exec(
            sprintf(
                '%s %s %s',
                $this->getBinary( self::XSLTPROC_BINARY ),
                $this->getXslStylesheetPath( 'header.xsl' ),
                $this->getCruiseControlLogPath()
            )
        );

        $this->assertSame(
            $this->loadExpectedResult(),
            trim( $result )
        );
    }

    /**
     * Loads a xsl stylesheet for the given local path.
     *
     * @param string $localPath The local stylesheet path starting from
     *        phpUnderControl's data/webapps/cruisecontrol/xsl directory.
     *
     * @return DOMDocument
     */
    private function createXslStylesheet( $localPath )
    {
        $xsl = new DOMDocument( '1.0', 'UTF-8' );
        $xsl->load( $this->getXslStylesheetPath( $localPath ) );

        return $xsl;
    }

    /**
     * Returns the absolute path of a xsl stylesheet file.
     *
     * @param string $localPath The local stylesheet path starting from
     *        phpUnderControl's data/webapps/cruisecontrol/xsl directory.
     *
     * @return string
     */
    private function getXslStylesheetPath( $localPath )
    {
        $absolutePath = PHPUC_DATA_DIR . self::WEBAPP_XSL_DIR . $localPath;
        if ( file_exists( $absolutePath ) )
        {
            return $absolutePath;
        }
        throw new ErrorException( 'Cannot locate xsl stylesheet ' . $localPath );
    }

    /**
     * Loads the contents of a file in the _expected directory.
     *
     * @return string
     */
    private function loadExpectedResult()
    {
        return trim( file_get_contents( $this->createExpectedResultAbsolutePath() ) );
    }

    /**
     * Creates the absolute path of a file with expected content.
     *
     * @return string
     */
    private function createExpectedResultAbsolutePath()
    {
        $absolutePath = sprintf(
            '%s/stylesheet/%s.html',
            PHPUC_TEST_EXPECTED,
            $this->getTestFunctionName()
        );

        if ( file_exists( $absolutePath ) )
        {
            return $absolutePath;
        }
        throw new ErrorException( 'Cannot locate expected file: ' . $absolutePath );
    }
}