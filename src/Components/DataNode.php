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

use DreamFactory\Library\Console\Interfaces\ConfigFileLike;
use DreamFactory\Library\Console\Interfaces\NodeLike;

/**
 * A fancy array
 */
class DataNode implements NodeLike, \IteratorAggregate, \Countable
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string My node ID
     */
    protected $_id;
    /**
     * @type array My values
     */
    protected $_contents;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param string $id     The ID/name of this node
     * @param array  $values An array of values to fill the node
     */
    public function __construct( $id, array $values = array() )
    {
        $this->_id = $id;
        $this->_contents = empty( $values ) ? $this->initializeNode() : $values;
    }

    /**
     * @param array $values
     *
     * @return \DreamFactory\Library\Console\Interfaces\NodeLike
     */
    public function add( array $values = array() )
    {
        foreach ( $values as $_key => $_value )
        {
            $this->set( $_key, $_value );
        }

        return $this;
    }

    /**
     * @param string   $id
     * @param NodeLike $node
     */
    public function addNode( $id, NodeLike &$node )
    {
        $this->add( array($id => $node->all()) );
    }

    /**
     * @param string   $id
     * @param NodeLike $node
     */
    public function setNode( $id, NodeLike $node )
    {
        $this->set( $id, $node->all() );
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
     * @return bool|string The normalized key if found, or false
     */
    public function has( $key )
    {
        $_key = $this->normalizeKey( $key );

        return array_key_exists( $_key, $this->_contents ) ? $_key : false;
    }

    /**
     * @inheritdoc
     */
    public function get( $key = null, $defaultValue = null, $burnAfterReading = false )
    {
        if ( null === $key )
        {
            return $this->all();
        }

        if ( false !== ( $_key = $this->has( $key ) ) )
        {
            return $this->_contents[$_key];
        }

        return $defaultValue;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @param bool   $overwrite
     *
     * @return NodeLike|ConfigFileLike
     */
    public function set( $key, $value, $overwrite = true )
    {
        if ( $value instanceof NodeLike )
        {
            $value = $value->all();
        }

        if ( !$overwrite && false !== ( $_key = $this->has( $key ) ) )
        {
            throw new \LogicException( 'The key "' . $key . '" exists and overwrite is disabled.' );
        }

        $this->_contents[$_key] = $value;

        return $this;
    }

    /**
     * Returns an array of all node entries
     *
     * @param string $format A data format in which to provide the results. Valid options are null and "json"
     *
     * @return array|string
     */
    public function all( $format = null )
    {
        switch ( $format )
        {
            case 'json':
                return json_encode( $this->_contents, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
        }

        return empty( $this->_contents ) ? array() : $this->_contents;
    }

    /**
     * @return NodeLike
     */
    public function initializeNode()
    {
        $this->_contents = $this->getDefaultSchema();
    }

    /**
     * @return array|NodeLike
     */
    public function getDefaultSchema()
    {
        return array();
    }

    /**
     * Normalizes a key for comparisons
     *
     * @param string $key
     *
     * @return string
     */
    public function normalizeKey( $key )
    {
        return strtolower( $key );
    }

    /**
     * @param string $key
     *
     * @return bool True if the key existed and was deleted
     */
    public function remove( $key )
    {
        if ( false !== ( $_key = $this->has( $key ) ) )
        {
            unset( $this->_contents[$_key] );

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new \ArrayIterator( $this->_contents );
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return count( $this->_contents );
    }
}
