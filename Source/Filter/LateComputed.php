<?php

declare(strict_types=1);

/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2007-2017, Hoa community. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the Hoa nor the names of its contributors may be
 *       used to endorse or promote products derived from this software without
 *       specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS AND CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Hoa\Stream\Filter;

use Hoa\Stream;

/**
 * Class \Hoa\Stream\Filter\LateComputed.
 *
 * A late computed filter computes the data when closing the filtering.
 */
abstract class LateComputed extends Basic
{
    /**
     * Buffer.
     */
    protected $_buffer = null;



    /**
     * Filter data.
     * This method is called whenever data is read from or written to the attach
     * stream.
     */
    public function filter($in, $out, int &$consumed, bool $closing): int
    {
        $return  = self::FEED_ME;
        $iBucket = new Stream\Bucket($in);

        while (false === $iBucket->eob()) {
            $this->_buffer .= $iBucket->getData();
            $consumed += $iBucket->getLength();
        }

        if (null !== $consumed) {
            $return = self::PASS_ON;
        }

        if (true === $closing) {
            $stream = $this->getStream();
            $this->compute();
            $bucket = new Stream\Bucket(
                $stream,
                Stream\Bucket::IS_A_STREAM,
                $this->_buffer
            );
            $oBucket = new Stream\Bucket($out);
            $oBucket->append($bucket);

            $return        = self::PASS_ON;
            $this->_buffer = null;
        }

        return $return;
    }

    /**
     * Compute the whole data (stored in $this->_buffer).
     */
    abstract protected function compute(): ?string;
}