#!/usr/bin/env php
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
 * @author     Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright  2007-2010 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id: phpuc.php 2010 2008-01-02 12:24:48Z mapi $
 * @link       http://www.phpundercontrol.org/
 */

if ( stripos( PHP_OS, 'WIN' ) !== false )
{
    $rmcmd      = 'rmdir /S /Q';
    $copycmd    = 'xcopy /S /E /I';
    $installDir = 'c:\Programme\CruiseControl';
}
else
{
    $rmcmd      = 'rm -rf';
    $copycmd    = 'cp -rf';
    $installDir = '/opt/cruisecontrol/current';
}
if ( count( $GLOBALS['argv'] ) > 1 )
{
    $installDir = $GLOBALS['argv'][1];
}

$config                     = new DOMDocument( '1.0', 'UTF-8' );
$config->formatOutput       = true;
$config->preserveWhiteSpace = false;
$config->load( $installDir . '/config.xml' );

$xpath = new DOMXPath( $config );
$result = $xpath->query( '//project[@name="php-under-control"]' );

if ( $result->length > 0 )
{
    $node = $result->item( 0 );
    $node->parentNode->removeChild( $node );

    $config->save( $installDir . '/config.xml' );
}

$projectDir = sprintf(
    "%s%sprojects%sphp-under-control",
    $installDir,
    DIRECTORY_SEPARATOR,
    DIRECTORY_SEPARATOR
);
if ( file_exists( $projectDir ) && is_dir( $projectDir ) )
{
    system( "{$rmcmd} {$projectDir}" );
}

$webappsOrig =  sprintf(
    "%s%swebapps%scruisecontrol.orig",
    $installDir,
    DIRECTORY_SEPARATOR,
    DIRECTORY_SEPARATOR
);
if ( file_exists( $webappsOrig ) && is_dir( $webappsOrig ) )
{
    $webapps = sprintf(
        "%s%swebapps%scruisecontrol",
        $installDir,
        DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR
    );

    system( "{$rmcmd} {$webapps}" );

    system( "{$copycmd} {$webappsOrig} {$webapps}" );
}
