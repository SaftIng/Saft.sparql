<?php

/*
 * This file is part of Saft.
 *
 * (c) Konrad Abicht <hi@inspirito.de>
 * (c) Natanael Arndt <arndt@informatik.uni-leipzig.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Saft\Sparql\Result;

/**
 * Represents an empty result, usually after an INSERT or UPDATE SPARQL query.
 */
class EmptyResultImpl implements Result
{
    /**
     * @return array
     */
    public function getVariables()
    {
        return [];
    }

    /**
     * @return bool True
     */
    public function isEmptyResult()
    {
        return true;
    }

    /**
     * @return bool False
     */
    public function isSetResult()
    {
        return false;
    }

    /**
     * @return bool False
     */
    public function isStatementSetResult()
    {
        return false;
    }

    /**
     * @return bool False
     */
    public function isValueResult()
    {
        return false;
    }
}
