<?php
/**
 * This file is part of the DreamFactory Console Tools Library
 *
 * Copyright 2014 DreamFactory Software, Inc. <support@dreamfactory.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace DreamFactory\Library\Console\Components;

use DreamFactory\Library\Console\Bags\BagOfHolding;

/**
 * A fancy array
 */
class DataNode extends BagOfHolding
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string The ID of this node
     */
    const NODE_ID = null;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param string $id
     * @param array  $contents
     */
    public function __construct( $id = null, array $contents = array() )
    {
        $_id = static::NODE_ID ?: $id;

        //  Override the ID with the current class's NODE_ID
        parent::__construct( $_id, $contents );
    }

    /**
     * Returns an array containing an empty schema for this node
     *
     * @param bool $addComment If true, a created comment will be added
     *
     * @return array
     */
    public function getSchema( $addComment = true )
    {
        return array();
    }
}
