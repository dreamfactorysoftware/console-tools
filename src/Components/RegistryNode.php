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
use DreamFactory\Library\Console\Interfaces\RegistryNodeLike;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * A simple object to load and store application options
 */
class RegistryNode extends ParameterBag implements RegistryNodeLike
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
    //* Members
    //******************************************************************************

    /**
     * @type RegistryLike My owner
     */
    protected $_parent;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @return RegistryLike
     */
    public function getParent()
    {
        return $this->_parent;
    }

    /**
     * @param RegistryLike $parent
     *
     * @return RegistryNode
     */
    public function setParent( $parent )
    {
        $this->_parent = $parent;

        return $this;
    }

}
