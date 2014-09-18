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
use DreamFactory\Tools\Fabric\Utility\CommandHelper;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * A configuration/settings container
 */
class ConfigNode extends ParameterBag implements ConfigNodeLike
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string Our meta node
     */
    const META_DATA_KEY = '_meta';

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string My node ID
     */
    protected $_nodeId;
    /**
     * @type string My parent node id, if any
     */
    protected $_parentId = null;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param string $parentId The ID/name of my parent node
     * @param string $nodeId   The ID/name of this node
     * @param array  $parameters
     */
    public function __construct( $parentId, $nodeId, array $parameters = array() )
    {
        $this->_nodeId = $nodeId;
        $this->_parentId = $parentId;

        parent::__construct( $parameters );
    }

    /**
     * Returns all entries in a JSON string
     *
     * @return string
     */
    public function allJson()
    {
        return json_encode( $this->all(), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
    }

    /**
     * Adds comment to the metadata for this node
     *
     * @param string $comment
     *
     * @return $this
     */
    public function addComment( $comment )
    {
        $_node = $this->getMetaData();
        $_node['comments'] = array_merge( $_node['comments'], $this->createEntryComment( $comment, false ) );
        $this->setMetaData( $_node );

        return $this;
    }

    /**
     * Gets the hive's metadata
     *
     * @return ConfigNodeLike|array
     */
    public function getMetaData()
    {
        if ( !$this->has( static::META_DATA_KEY ) )
        {
            $this->set( static::META_DATA_KEY, $this->getDefaultMetaData() );
        }

        return $this->get( static::META_DATA_KEY );
    }

    /**
     * Sets the hive's metadata
     *
     * @param array|ConfigNodeLike $metaData
     *
     * @return $this
     */
    public function setMetaData( array $metaData = array() )
    {
        $this->set( static::META_DATA_KEY, $metaData );

        return $this;
    }

    /**
     * @return array
     */
    public function getDefaultMetaData()
    {
        $_metaData = array(
            'node_id'    => $this->_nodeId,
            'parent_id'  => $this->_parentId,
            'comments'   => $this->createEntryComment( 'Creation', false ),
            'updated_at' => CommandHelper::getCurrentTimestamp(),
        );

        return new static( $this->_nodeId, static::META_DATA_KEY, $_metaData );
    }

    /**
     * @return array|ConfigNodeLike
     */
    public function getDefaultSchema()
    {
        return $this->getDefaultMetaData();
    }

    /**
     * @return array
     */
    public function getDefaultNodeSchema()
    {
        return array();
    }

    /**
     * Locates an entry within a node
     *
     * @param string $entryId     The entry within the node
     * @param bool   $autoCreate  Inits the node and entry if the keys aren't found
     * @param bool   $returnValue If found, and this is true, the entry is returned, otherwise TRUE
     *
     * @return bool|array
     */
    public function hasEntry( $entryId, $autoCreate = false, $returnValue = false )
    {
        if ( !$this->has( $entryId ) )
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
     * @return string
     */
    public function getParentId()
    {
        return $this->_parentId;
    }

    /**
     * @return string The id of this node
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param string $key
     *
     * @return bool True if the key exists in the node
     */
    public function contains( $key )
    {
        return $this->has( $key );
    }

    /**
     * @param string $key
     *
     * @return bool True if the key existed and was deleted
     */
    public function delete( $key )
    {
        $this->remove( $key );
    }
}
