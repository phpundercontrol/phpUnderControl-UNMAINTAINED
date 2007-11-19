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

require_once 'PHPUnit/Framework/TestCase.php';

require_once dirname(__FILE__) . '/../src/Math.php';

/**
 * Simple math test class.
 *
 * @category   Example
 * @package    PhpUnderControl
 * @subpackage Example
 * @author     Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright  2007 Manuel Pichler. All rights reserved.
 * @license    GPL http://www.gnu.org/licenses/gpl-3.0.txt
 * @version    Release: <package_version>
 * @link       http://www.manuel-pichler.de/
 */
class PhpUnderControl_Example_MathTest extends PHPUnit_Framework_TestCase
{
    /**
     * The used math object.
     *
     * @var PhpUnderControl_Example_Math $math
     */
    protected $math = null;

    /**
     * Creates a new {@link PhpUnderControl_Example_Math} object.
     */
    public function setUp()
    {
        parent::setUp();

        $this->math = new PhpUnderControl_Example_Math();
    }

    /**
     * Successful test.
     */
    public function testAddSuccess()
    {
        $this->assertEquals(4, $this->math->add(1, 3));
    }

    /**
     * Successful test.
     */
    public function testSubSuccess()
    {
        $this->assertEquals(-2, $this->math->sub(1, 3));
    }
}
