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
namespace DreamFactory\Library\Console\Utility;

use DreamFactory\Tools\Fabric\Exceptions\FabricException;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Some utilities for working with Eloquent outside of Laravel
 */
class EloquentHelper
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string The default pattern of files to auto-discover "_*.database.config.php" is the default.
     */
    const DEFAULT_CONFIG_PATTERN = '_*.database.config.php';

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type Capsule
     */
    protected static $_capsule = null;
    /**
     * @type array The complete database configuration
     */
    protected static $_config = array();

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Discovers individual database configurations in $path that match $pattern.
     * Discovered configurations are added to the database manager.
     *
     * See config/_default.database.config.php-dist for examples
     *
     * @param string $path
     * @param string $pattern
     *
     * @throws FabricException
     * @return bool True if files were autoloaded
     */
    public static function autoload( $path, $pattern = self::DEFAULT_CONFIG_PATTERN )
    {
        $_files = glob( $path . DIRECTORY_SEPARATOR . $pattern, GLOB_MARK );
        $_found = false;

        foreach ( $_files as $_file )
        {
            if ( '.' == $_file || '..' == $_file )
            {
                continue;
            }

            if ( is_file( $_file ) )
            {
                /** @noinspection PhpIncludeInspection */
                $_config = include( $_file );

                if ( is_array( $_config ) )
                {
                    foreach ( $_config as $_id => $_dbConfig )
                    {
                        static::addConnection( $_dbConfig, $_id );
                        $_found = true;
                    }
                }
            }
        }

        if ( !$_found )
        {
            throw new FabricException( 'No database configuration found.' );
        }

        return $_found;
    }

    /**
     * Register a connection with the manager.
     *
     * @param  array  $config
     * @param  string $name
     *
     * @return void
     */
    public static function addConnection( array $config = array(), $name = null )
    {
        if ( null === static::$_capsule )
        {
            static::$_capsule = new Capsule();
        }

        static::$_capsule->addConnection( $config, $name );
    }

    /**
     * @return Capsule
     */
    public static function getCapsule()
    {
        return static::$_capsule;
    }

    /**
     * Optionally sets this config as the global and boots Eloquent
     *
     * @param bool $asGlobal If true this configuration will be available globally
     */
    public static function boot( $asGlobal = true )
    {
        if ( $asGlobal )
        {
            static::$_capsule->setAsGlobal();
        }

        static::$_capsule->bootEloquent();
    }

}