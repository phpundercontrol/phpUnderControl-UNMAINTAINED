#!/usr/bin/env php
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
 */

/**
 * Autoload function for the checkout version of phpUnderControl
 *
 * @param string $className The class name.
 * 
 * @return void
 */
function __autoload( $className )
{
    if ( strpos( $className, 'puc' ) === 0 )
    {
        $fileName = sprintf(
            '%s/../src/%s.php',
            dirname( __FILE__ ),
            substr( $className, 3 )
        );
        
        include $fileName;
    }
}

$installer = new pucPhpUnderControl();
$installer->run();