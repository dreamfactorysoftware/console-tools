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
use DreamFactory\Library\Console\Utility\CommandHelper;

/**
 * A fancy array
 */
class DataNode implements NodeLike
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string My node ID
     */
    protected $_id;
    /**
     * @type string My parent node id, if any
     */
    protected $_parentId = null;
    /**
     * @type array My values
     */
    protected $_contents;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param string $id       The ID/name of this node
     * @param array  $values   An array of values to fill the node
     * @param string $parentId The ID/name of my parent node, if any
     */
    public function __construct( $id, array $values = array(), $parentId = null )
    {
        $this->_id = $id;
        $this->_parentId = $parentId;
        $this->_contents = $values;

        //  This initializes the metadata
        $this->getMetaData();
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
     * Adds comment to the metadata for this node
     *
     * @param string $comment
     *
     * @return $this
     */
    public function addComment( $comment )
    {
        $_node = $this->getMetaData();
        $_node['comments'] = array_merge( $_node['comments'], $this->createComment( $comment ) );
        $this->setMetaData( $_node );

        return $this;
    }

    /**
     * Gets the hive's metadata
     *
     * @return NodeLike|array
     */
    public function getMetaData()
    {
        if ( !$this->contains( static::META_DATA_KEY ) )
        {
            $this->setMetaData( $this->getDefaultMetaData() );
        }

        return $this->getMetaData();
    }

    /**
     * Sets the hive's metadata
     *
     * @param array|NodeLike $metaData
     *
     * @return $this
     */
    public function setMetaData( array $metaData = array() )
    {
        $this->set( static::META_DATA_KEY, $metaData );

        return $this;
    }

    /**
     * @param bool $createComment If true, a "created" comment is added to the schema
     *
     * @return array
     */
    public function getDefaultMetaData( $createComment = true )
    {
        $_metadata = array(
            'id'         => $this->_id,
            'parent_id'  => $this->_parentId,
            'comments'   => array(),
            'updated_at' => CommandHelper::getCurrentTimestamp(),
        );

        if ( $createComment )
        {
            $_metadata['comments'][] = $this->createComment( 'Creation' );
        }

        return $_metadata;
    }

    /**
     * @return array|NodeLike
     */
    public function getDefaultSchema()
    {
        return array(static::META_DATA_KEY => $this->getDefaultMetaData());
    }

    /**
     * @param string $comment
     *
     * @return array
     */
    public function createComment( $comment )
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
        return array_key_exists( $this->normalizeKey( $key ), $this->_contents );
    }

    /**
     * @param string $key
     *
     * @return bool True if the key existed and was deleted
     */
    public function delete( $key )
    {
        if ( $this->contains( $key ) )
        {
            unset( $this->_contents[$this->normalizeKey( $key )] );

            return true;
        }

        return false;
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

        $_key = $this->normalizeKey( $key );

        if ( $this->contains( $_key ) )
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

        if ( !$overwrite && $this->contains( $key ) )
        {
            throw new \LogicException( 'The key "' . $key . '" exists and overwrite is disabled.' );
        }

        $this->_contents[$this->normalizeKey( $key )] = $value;

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
}
