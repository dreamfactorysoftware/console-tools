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
 * ConfigKeys
 * Enumerations of pre-defined application config keys
 */
class ConfigKeys extends SeedEnum
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
     * @type string The level key for storing servers
     */
    const SERVER_ID = 'server-id';
    /**
     * @type string The top-level key for storing db servers
     */
    const DB_SERVERS = 'db-servers';
    /**
     * @type string The top-level key for storing web servers
     */
    const WEB_SERVERS = 'web-servers';
    /**
     * @type string The top-level key for storing app servers
     */
    const APP_SERVERS = 'app-servers';
}
