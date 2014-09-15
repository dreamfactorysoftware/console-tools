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
namespace DreamFactory\Library\Console\Enums;

use Kisma\Core\Enums\SeedEnum;

/**
 * RegistryKeys
 * Enumerations of pre-defined application registry keys
 */
class RegistryKeys extends SeedEnum
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type int
     */
    const TYPE_UNDEFINED = 0;
    /**
     * @type int
     */
    const TYPE_SCALAR = 1;
    /**
     * @type int
     */
    const TYPE_ARRAY = 2;
    /**
     * @type int
     */
    const TYPE_OBJECT = 3;
    /**
     * @type int
     */
    const TYPE_DIRECTORY = 4;

    /**
     * @type string The top-level key for storing servers
     */
    const SERVERS = 'servers';
    /**
     * @type string The level key for storing servers
     */
    const DB_SERVERS = 'db';
    /**
     * @type string The level key for storing servers
     */
    const DB_SERVERS_PK = 'db-server-id';
    /**
     * @type string The 2nd level key for storing servers
     */
    const WEB_SERVERS_PK = 'web-server-id';
    /**
     * @type string The 2nd level key for storing servers
     */
    const APP_SERVERS_PK = 'app-server-id';
}
