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

use DreamFactory\Library\Console\Interfaces\RegistryLike;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * A simple object to load and store application options
 */
class Registry extends ParameterBag
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string The name of the directory containing our configuration
     */
    const DEFAULT_REGISTRY_BASE = '.dreamfactory';
    /**
     * @type string The name of the directory containing our configuration
     */
    const DEFAULT_REGISTRY_SUFFIX = '.registry.json';
    /**
     * @type string The format to use when creating date strings
     */
    const DEFAULT_TIMESTAMP_FORMAT = 'c';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Adds node entry with overwrite protection
     *
     * @param string $nodeId
     * @param string $entryId
     * @param array  $properties
     * @param bool   $overwrite
     *
     * @return Registry
     */
    public function addNodeEntry( $nodeId, $entryId, $properties = array(), $overwrite = false )
    {
        $_nodes = $this->all();

        if ( !array_key_exists( $nodeId, $_nodes ) )
        {
            $this->add( $nodeId, array() );
            $_nodes[$nodeId] = array();
        }

        if ( array_key_exists( $entryId, $_nodes[$nodeId] ) && !$overwrite )
        {
            throw new \InvalidArgumentException( 'The entry id "' . $entryId . '" exists and $overwrite is not TRUE' );
        }

        return $this->setNodeEntry( $nodeId, $nodeId, $properties );
    }

    /**
     * Sets a node entry
     *
     * @param string $nodeId
     * @param string $entryId
     * @param array  $properties
     *
     * @return Registry
     */
    public function setNodeEntry( $nodeId, $entryId, $properties = array() )
    {
        $_nodes = $this->all();

        if ( !array_key_exists( $nodeId, $_nodes ) )
        {
            $_nodes[$nodeId] = array();
        }

        $_nodes[$nodeId][$entryId] = $properties;

        $this->clear();
        $this->add( $_nodes );

        return $this;
    }

    /**
     * @param string $nodeId
     * @param string $entryId
     * @param mixed  $defaultValue
     *
     * @return mixed
     */
    public function getNodeEntry( $nodeId, $entryId, $defaultValue = array() )
    {
        $_nodes = $this->all();

        return !array_key_exists( $nodeId, $_nodes ) ? $defaultValue : $_nodes[$entryId];
    }

    /**
     * Creates a new registry and loads from a JSON file
     *
     * @param string $filePath The absolute path to a JSON file
     *
     * @throws \Kisma\Core\Exceptions\FileSystemException
     * @return RegistryLike
     */
    public static function createFromFile( $filePath )
    {
        $_jsonFile = new JsonFile( $filePath );

        return new static( $_jsonFile->read() );
    }

    /**
     * @param string $fileName
     *
     * @throws \Exception
     */
    public function save( $fileName )
    {
        $_jsonFile = new JsonFile( $fileName );

        $_jsonFile->write( $this->all() );
    }
}
