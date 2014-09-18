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
namespace DreamFactory\Library\Console\Interfaces;

use Kisma\Core\Interfaces\BagLike;

interface NodeLike extends BagLike
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string Our metadata key
     */
    const META_DATA_KEY = '_metadata';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Returns the parent node id of this node, if any.
     *
     * @return string the id of my parent node
     */
    public function getParentId();

    /**
     * @return string The id of this node
     */
    public function getId();

    /**
     * @param string $key
     *
     * @return bool True if the key exists in the node
     */
    public function contains( $key );

    /**
     * @param string $key              The key to get. If null, all values are returned
     * @param mixed  $defaultValue
     * @param bool   $burnAfterReading If true, key is removed from bag after reading
     *
     * @return mixed
     */
    public function get( $key = null, $defaultValue = null, $burnAfterReading = false );

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return NodeLike
     */
    public function set( $key, $value, $overwrite = true );

    /**
     * @param string $key
     *
     * @return bool True if the key existed and was deleted
     */
    public function delete( $key );

    /**
     * Returns an array of all node entries
     *
     * @param string $format A data format in which to provide the results. Valid options are null and "json"
     *
     * @return array|string
     */
    public function all( $format = null );
}