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
namespace DreamFactory\Library\Console\Bags;

use DreamFactory\Library\Console\Components\DataNode;

/**
 * A bag that holds nodes
 */
class NodeBag extends BagOfHolding
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
     * A default schema for the node
     *
     * @return array
     */
    public function getSchema()
    {
        return array();
    }

    /**
     * @param DataNode[] $nodes
     *
     * @return \DreamFactory\Library\Console\Components\NodeBag
     */
    public function add( array $nodes = array() )
    {
        foreach ( $nodes as $_node )
        {
            $this->set( $_node->getId(), $_node );
        }
    }

    /**
     * @param array $contents
     *
     * @return $this
     */
    public function replace( $contents = array() )
    {
        if ( $contents instanceof DataNode )
        {
            return $this->set( $contents->getId(), $contents );
        }

        return parent::replace( $contents );
    }

    /**
     * @param array|DataNode $contents
     *
     * @return $this|BagOfHolding
     */
    public function initialize( $contents = null )
    {
        if ( $contents instanceof DataNode )
        {
            return $this->set( $contents->getId(), $contents );
        }

        if ( is_array( $contents ) )
        {
            foreach ( $contents as $_key => $_value )
            {
                $this->set( $_key, $_value );
            }

            return $this;
        }

        //  I have no idea what you've sent in here...
        return $this->clear();
    }

}
