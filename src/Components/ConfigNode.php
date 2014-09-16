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
     * @type string The name of the directory containing our configuration
     */
    const DEFAULT_CONFIG_BASE = '.dreamfactory';
    /**
     * @type string The name of the directory containing our configuration
     */
    const DEFAULT_CONFIG_SUFFIX = '.registry.json';
    /**
     * @type string The format to use when creating date strings
     */
    const DEFAULT_TIMESTAMP_FORMAT = 'c';

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
    /**
     * @type int
     */
    protected $_currentKeyIndex = 0;

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
        $_node = $this->get( $this->_nodeId );
        $_node['_meta']['comments'] = array_merge( $_node['_meta']['comments'], $this->_createComment( $comment, false ) );
        $this->set( $this->_nodeId, $_node );

        return $this;
    }

    /**
     * @param string $comment
     *
     * @return array
     */
    protected function _createComment( $comment )
    {
        return array(date( static::DEFAULT_TIMESTAMP_FORMAT ) => $comment);
    }

    /**
     * @param bool   $wrapNode If FALSE, the contents of the node._meta is returned. Otherwise nodeId[node._meta] is returned
     * @param string $parentId My parent node id, if any
     *
     * @return array An array suitable for "$this->add()"
     */
    public function getDefaultSchema( $wrapNode = true, $parentId = null )
    {
        $this->_parentId = $parentId;

        $_node = array(
            '_meta' => array(
                'parentId'   => $parentId,
                'comments'   => $this->_createComment( 'Creation', false ),
                'updated_at' => date( static::DEFAULT_TIMESTAMP_FORMAT ),
            )
        );

        if ( $wrapNode )
        {
            $_node = array($this->_nodeId => $_node);
        }

        return $_node;
    }

    /**
     * @return string
     */
    public function getParentId()
    {
        return $this->_parentId;
    }

    /**
     * @param string $parentId
     *
     * @return ConfigNode
     */
    public function setParentId( $parentId )
    {
        $this->_parentId = $parentId;

        return $this;
    }

    /**
     * @return string The id of this node
     */
    public function getNodeId()
    {
        return $this->_nodeId;
    }
}
