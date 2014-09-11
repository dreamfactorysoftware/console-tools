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

/**
 * A simple registry
 */
class Registry
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
    const DEFAULT_CONFIG_SUFFIX = '.options.json';
    /**
     * @type string The format to use when creating date strings
     */
    const DEFAULT_TIMESTAMP_FORMAT = 'c';

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string
     */
    protected $_registryKey = null;
    /**
     * @type array
     */
    protected $_contents = array();

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param string $entryId     The entry within the registry
     * @param bool   $autoCreate  Inits the hive if the key isn't found
     * @param bool   $returnValue If found, and this is true, the value stored at key $registryKey is returned, otherwise TRUE
     *
     * @internal param array $registry
     * @internal param string $registryKey
     * @return bool|array
     */
    public function hasEntry( $entryId, $autoCreate = false, $returnValue = false )
    {
        if ( !array_key_exists( $entryId, $this->_contents ) || isset( $this->_contents[$entryId] ) )
        {
            if ( !$autoCreate )
            {
                return true;
            }

            $this->_contents[$entryId] = $this->_initializeContents();
        }

        return $returnValue ? $this->_contents[$entryId] : true;
    }

    /**
     * Returns the value stored under the registry key $registryKey. Returns FALSE on not-found
     *
     * @param string $registryKey
     * @param string $entryId
     * @param bool   $autoCreate Auto-create the entry if it does not exist
     *
     * @return bool|array
     */
    public function getEntry( $entryId, $autoCreate = true )
    {
        return $this->hasEntry( $entryId, $autoCreate, true );
    }

    /**
     * @param string $registryKey
     * @param string $entryId
     * @param array  $properties
     *
     * @return $this
     */
    public function addEntry( $entryId, array $properties = array() )
    {
        $_entry = $this->getEntry( $entryId, true );

        $this->_contents[$entryId] = array_merge( $_entry, $properties );

        return $this;
    }

    /**
     * Removes a registry from the config
     *
     * @param string $registryKey
     * @param string $entryId
     *
     * @return bool
     */
    public function removeRegistryEntry( $registryKey, $entryId )
    {
        if ( !$this->hasEntry( $entryId, false ) )
        {
            return false;
        }

        unset( $this->_contents[$registryKey][$entryId] );

        return true;
    }

    /**
     * @param array $registry An existing registry
     *
     * @return array
     */
    protected function _mergeRegistry( array $registry = array() )
    {
        return array_merge( $this->_contents, $registry );
    }

    /**
     * @return array The default registry values
     */
    protected function _initializeContents()
    {
        return array(
            '_comments'  => array(),
            '_timestamp' => date( 'Ymd HiS' ),
        );
    }
}
