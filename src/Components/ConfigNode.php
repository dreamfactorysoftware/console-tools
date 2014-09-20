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

use DreamFactory\Library\Console\Interfaces\ConfigNodeLike;
use DreamFactory\Library\Console\Interfaces\NodeLike;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;

/**
 * A configuration/settings container
 */
class ConfigNode extends NodeBag
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Locates an entry within a node
     *
     * @param string $entryId     The entry within the node
     * @param bool   $autoCreate  Initializes the node and entry if the keys aren't found
     * @param bool   $returnValue If found, and this is true, the entry is returned, otherwise TRUE
     *
     * @return bool|array
     */
    public function hasEntry( $entryId, $autoCreate = false, $returnValue = false )
    {
        $this->
        if ( !$this->contains( $entryId ) )
        {
            if ( !$autoCreate )
            {
                throw new ParameterNotFoundException( $entryId );
            }

            $this->add( array($entryId => $this->getDefaultNodeSchema()) );
        }

        return $returnValue ? $this->get( $entryId ) : true;
    }

    /**
     * Returns the value stored under the registry key $registryKey. Returns FALSE on not-found
     *
     * @param string $entryId
     * @param bool   $autoCreate Auto-create the entry if it does not exist
     *
     * @internal param string $nodeId
     * @return bool|array
     */
    public function getEntry( $entryId, $autoCreate = true )
    {
        return $this->hasEntry( $entryId, $autoCreate, true );
    }

    /**
     * @param string $entryId
     * @param array  $parameters
     *
     * @return $this
     */
    public function addEntry( $entryId, array $parameters = array() )
    {
        $_node = $this->hasEntry( $entryId, true, true );
        $this->set( $entryId, array_merge( $_node, $parameters ) );

        return $this;
    }

    /**
     * Removes a registry from the config
     *
     * @param string $nodeId
     * @param string $entryId
     *
     * @return bool
     */
    public function removeEntry( $nodeId, $entryId )
    {
        if ( false === ( $_node = $this->hasEntry( $entryId, false, true ) ) )
        {
            return false;
        }

        unset( $_node[$nodeId] );
        $this->set( $nodeId, $_node );

        return true;
    }

    /**
     * @param string $comment
     *
     * @return array
     */
    public function createEntryComment( $comment )
    {
        return array(CommandHelper::getCurrentTimestamp() => $comment);
    }

    /**
     * Returns an array that will become the contents of a new configuration node
     *
     * @return array|ConfigNodeLike
     */
    public function getDefaultSchema()
    {
        // TODO: Implement getDefaultSchema() method.
    }

    /**
     * Initializes the contents of the bag
     *
     * @param array $contents
     *
     * @return NodeLike
     */
    public function initialize( array $contents = array() )
    {
        // TODO: Implement initialize() method.
    }
}
