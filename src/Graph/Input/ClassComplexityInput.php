<?php
/**
 * This file is part of phpUnderControl.
 *
 * PHP Version 5.2.0
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
 * @category   QualityAssurance
 * @package    Graph
 * @subpackage Input
 * @author     Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright  2007-2010 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpundercontrol.org/
 */

/**
 *
 *
 * @category   QualityAssurance
 * @package    Graph
 * @subpackage Input
 * @author     Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright  2007-2010 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpundercontrol.org/
 */
class phpucClassComplexityInput extends phpucAbstractInput
{
    /**
     * Constructs a new class complexity input object.
     */
    public function __construct()
    {
        parent::__construct(
            'Class Complexity',
            'thumbs/class-complexity',
            phpucChartI::TYPE_BAR
        );

        $this->yAxisLabel = 'Classes';
        $this->xAxisLabel = 'Complexity ';

        $this->addRule(
            new phpucInputRule(
                'Temp',
                '/cruisecontrol/metrics/package/class/@wmc',
                self::MODE_LIST
            )
        );
    }

    /**
     * Post processes the fetched data.
     *
     * Concrete implementations can overwrite this this method to post process
     * the fetched data before it is given to the graph object. This can be very
     * usefull in all cases where logs don't have the required format.
     *
     * @param array(string=>array) $logs Fetched log data.
     *
     * @return array(string=>mixed)
     */
    protected function postProcessLog( array $logs )
    {
        $result = array();
        foreach ( $logs['Temp'] as $complexity )
        {
            $complexity = ceil( $complexity / 5 ) * 5;
            if ( !isset( $result[$complexity] ) )
            {
                $result[$complexity] = 0;
            }
            ++$result[$complexity];
        }

        $size = 5;
        while ( count( $result ) > 8 )
        {
            $size += 5;
            $temp = array();
            foreach ( $result as $complexity => $count )
            {
                $complexity = ceil( $complexity / $size ) * $size;
                if ( !isset( $temp[$complexity] ) )
                {
                    $temp[$complexity] = 0;
                }
                $temp[$complexity] += $count;
            }
            $result = $temp;

        }

        if ( count($result) < 2 )
        {
            $result[0] = 0;
        }
        ksort( $result );

        return array('Temp' => $result);
    }
}