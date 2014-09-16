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

use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * A simple settings registry
 */
class SettingsNode extends ParameterBag
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string The ID of this node
     */
    protected $_nodeId;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Creates a new SettingsNodes
     *
     * @param string $nodeId
     * @param array  $settings
     *
     * @return static
     */
    public static function createNode( $nodeId, array $settings = array() )
    {
        //  Create a new bag and put our junk in it
        /** @type SettingsNode $_node */
        $_node = new static();
        $_node->setNodeId( $nodeId )->add( array($nodeId => $settings) );

        return $_node;
    }

    /**
     * Sets the node ID of this node
     *
     * @param string $nodeId
     *
     * @return SettingsNode
     */
    public function setNodeId( $nodeId )
    {
        $this->_nodeId = $nodeId;

        return $this;
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
    public function hasEntry( $nodeId, $entryId, $autoCreate = false, $returnValue = false )
    {
        try
        {
            $_node = $this->get( $nodeId );
        }
        catch ( ParameterNotFoundException $_ex )
        {
            if ( !$autoCreate )
            {
                throw new \InvalidArgumentException( 'The node "' . $nodeId . '" does not exist.' );
            }

            //  Create a new node
            $_node = array($entryId => $this->_initializeNodeEntry());
        }

        if ( !array_key_exists( $entryId, $_node ) )
        {
            if ( !$autoCreate )
            {
                throw new \InvalidArgumentException( 'The entry "' . $entryId . '" does not exist in node.' );
            }

            $_node->add( array($entryId => $this->_initializeNodeEntry()) );
            $this->add( $_node );
        }

        $this->add( $_node );

        return $returnValue ? $_node[$entryId] : true;
    }

    /**
     * Returns the value stored under the registry key $registryKey. Returns FALSE on not-found
     *
     * @param string $nodeId
     * @param string $entryId
     * @param bool   $autoCreate Auto-create the entry if it does not exist
     *
     * @internal param string $registryKey
     * @return bool|array
     */
    public function getEntry( $nodeId, $entryId, $autoCreate = true )
    {
        return $this->hasEntry( $nodeId, $entryId, $autoCreate, true );
    }

    /**
     * @param string $nodeId
     * @param string $entryId
     * @param array  $properties
     *
     * @return $this
     */
    public function addEntry( $nodeId, $entryId, array $properties = array() )
    {
        $_node = $this->hasEntry( $nodeId, $entryId, true, true );

        if ( !array_key_exists( $entryId, $_node ) )
        {
            $_node[$entryId] = array($entryId => $this->_initializeNodeEntry());
        }

        $_node[$entryId] = array_merge( $_node[$entryId], $properties );
        $this->set( $nodeId, $_node );

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
     * @return array The default registry values
     */
    protected function _initializeNodeEntry()
    {
        return array(
            '_comments'  => array(),
            '_timestamp' => date( 'Ymd HiS' ),
        );
    }

    //******************************************************************************
    //* Bag Operations
    //******************************************************************************

    /**
     * Clears all parameters.
     */
    public function clear()
    {
        $this->_bag->clear();
    }

    /**
     * Adds parameters to the service container parameters.
     *
     * @param array $parameters An array of parameters
     */
    public function add( array $parameters )
    {
        $this->_bag->add( $parameters );
    }

    /**
     * Gets the service container parameters.
     *
     * @return array An array of parameters
     *
     * @api
     */
    public function all()
    {
        return $this->_bag->all();
    }

    /**
     * Returns all entries in a JSON string
     *
     * @return string
     */
    public function allJson()
    {
        return json_encode( $this->_bag->all(), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
    }

    /**
     * Gets a service container parameter.
     *
     * @param string $name The parameter name
     *
     * @return mixed  The parameter value
     *
     * @throws ParameterNotFoundException if the parameter is not defined
     *
     * @api
     */
    public function get( $name )
    {
        return $this->_bag->get( $name );
    }

    /**
     * Sets a service container parameter.
     *
     * @param string $name  The parameter name
     * @param mixed  $value The parameter value
     *
     * @api
     */
    public function set( $name, $value )
    {
        $this->_bag->set( $name, $value );
    }

    /**
     * Returns true if a parameter name is defined.
     *
     * @param string $name The parameter name
     *
     * @return bool    true if the parameter name is defined, false otherwise
     *
     * @api
     */
    public function has( $name )
    {
        $this->_bag->has( $name );
    }

    /**
     * Replaces parameter placeholders (%name%) by their values for all parameters.
     */
    public function resolve()
    {
        $this->_bag->resolve();
    }

    /**
     * Replaces parameter placeholders (%name%) by their values.
     *
     * @param mixed $value A value
     *
     * @throws ParameterNotFoundException if a placeholder references a parameter that does not exist
     */
    public function resolveValue( $value )
    {
        $this->_bag->resolveValue( $value );
    }

    /**
     * Escape parameter placeholders %
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function escapeValue( $value )
    {
        $this->_bag->escapeValue( $value );
    }

    /**
     * Unescape parameter placeholders %
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function unescapeValue( $value )
    {
        return $this->_bag->unescapeValue( $value );
    }
}
