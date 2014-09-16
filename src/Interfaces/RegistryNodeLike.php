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

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Something that acts like a registry node
 */
interface RegistryNodeLike extends ParameterBagInterface
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Returns the ID in which this node lives
     *
     * @return RegistryLike
     */
    public function getParent();

    /**
     * Sets the ID of my registry
     *
     * @param RegistryLike $parent
     *
     * @return $this
     */
    public function setRegistryId( RegistryLike $parent );

}
 