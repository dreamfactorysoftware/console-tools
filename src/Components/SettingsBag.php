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
 * A container for service/app settings
 */
class SettingsBag implements \IteratorAggregate, \Countable
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type array
     */
    protected $_settings = array();

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Construct a bag
     *
     * @param array $settings An array of settings
     */
    public function __construct( array $settings = array() )
    {
        $this->add( $settings );
    }

    /**
     * Returns the settings as a string.
     *
     * @return string The settings
     */
    public function __toString()
    {
        if ( empty( $this->_settings ) )
        {
            return '';
        }

        $_max = max( array_map( 'strlen', array_keys( $this->_settings ) ) ) + 1;
        $_content = null;

        ksort( $this->_settings );

        foreach ( $this->_settings as $_key => $_values )
        {
            $_name = implode( '-', array_map( 'ucfirst', explode( '-', $_key ) ) );

            foreach ( $_values as $_value )
            {
                $_content .= sprintf( "%-{$_max}s %s\r\n", $_name . ':', $_value );
            }
        }

        return $_content;
    }

    /**
     * Returns all settings in an array
     *
     * @return array
     */
    public function all()
    {
        return $this->_settings;
    }

    /**
     * Returns all the settings keys
     *
     * @return array
     */
    public function keys()
    {
        return array_keys( $this->_settings );
    }

    /**
     * Replaces the current settings with the new ones
     *
     * @param array $settings The new settings
     */
    public function replace( array $settings = array() )
    {
        $this->_settings = array();

        $this->add( $settings );
    }

    /**
     * Adds new settings the current settings set.
     *
     * @param array $settings An array of settings
     */
    public function add( array $settings )
    {
        foreach ( $settings as $_key => $_values )
        {
            $this->set( $_key, $_values );
        }
    }

    /**
     * Returns a setting value by name.
     *
     * @param string $key          The setting key
     * @param mixed  $defaultValue The default value
     * @param bool   $first        If true, return the first value found. Otherwise all matching key values are returned
     *
     * @return mixed|array
     */
    public function get( $key, $defaultValue = null, $first = true )
    {
        $key = strtr( strtolower( $key ), '_', '-' );

        if ( !array_key_exists( $key, $this->_settings ) )
        {
            if ( null === $defaultValue )
            {
                return $first ? null : array();
            }

            return $first ? $defaultValue : array($defaultValue);
        }

        if ( $first )
        {
            return count( $this->_settings[$key] ) ? $this->_settings[$key][0] : $defaultValue;
        }

        return $this->_settings[$key];
    }

    /**
     * Sets a header by name.
     *
     * @param string       $key     The key
     * @param string|array $values  The value or an array of values
     * @param bool         $replace Whether to replace the actual value or not (true by default)
     *
     * @api
     */
    public function set( $key, $values, $replace = true )
    {
        $key = strtr( strtolower( $key ), '_', '-' );

        $values = array_values( (array)$values );

        if ( true === $replace || !isset( $this->_settings[$key] ) )
        {
            $this->_settings[$key] = $values;
        }
        else
        {
            $this->_settings[$key] = array_merge( $this->_settings[$key], $values );
        }
    }

    /**
     * Returns true if the HTTP header is defined.
     *
     * @param string $key The HTTP header
     *
     * @return bool    true if the parameter exists, false otherwise
     *
     * @api
     */
    public function has( $key )
    {
        return array_key_exists( strtr( strtolower( $key ), '_', '-' ), $this->_settings );
    }

    /**
     * Returns true if the given HTTP header contains the given value.
     *
     * @param string $key   The HTTP header name
     * @param string $value The HTTP value
     *
     * @return bool    true if the value is contained in the header, false otherwise
     *
     * @api
     */
    public function contains( $key, $value )
    {
        return in_array( $value, $this->get( $key, null, false ) );
    }

    /**
     * Removes a header.
     *
     * @param string $key The HTTP header name
     *
     * @api
     */
    public function remove( $key )
    {
        $key = strtr( strtolower( $key ), '_', '-' );

        unset( $this->_settings[$key] );

        if ( 'cache-control' === $key )
        {
            $this->cacheControl = array();
        }
    }

    /**
     * Returns the HTTP header value converted to a date.
     *
     * @param string    $key     The parameter key
     * @param \DateTime $default The default value
     *
     * @return null|\DateTime The parsed DateTime or the default value if the header does not exist
     *
     * @throws \RuntimeException When the HTTP header is not parseable
     *
     * @api
     */
    public function getDate( $key, \DateTime $default = null )
    {
        if ( null === $value = $this->get( $key ) )
        {
            return $default;
        }

        if ( false === $date = \DateTime::createFromFormat( DATE_RFC2822, $value ) )
        {
            throw new \RuntimeException( sprintf( 'The %s HTTP header is not parseable (%s).', $key, $value ) );
        }

        return $date;
    }

    public function addCacheControlDirective( $key, $value = true )
    {
        $this->cacheControl[$key] = $value;

        $this->set( 'Cache-Control', $this->getCacheControlHeader() );
    }

    public function hasCacheControlDirective( $key )
    {
        return array_key_exists( $key, $this->cacheControl );
    }

    public function getCacheControlDirective( $key )
    {
        return array_key_exists( $key, $this->cacheControl ) ? $this->cacheControl[$key] : null;
    }

    public function removeCacheControlDirective( $key )
    {
        unset( $this->cacheControl[$key] );

        $this->set( 'Cache-Control', $this->getCacheControlHeader() );
    }

    /**
     * Returns an iterator for settings.
     *
     * @return \ArrayIterator An \ArrayIterator instance
     */
    public function getIterator()
    {
        return new \ArrayIterator( $this->_settings );
    }

    /**
     * Returns the number of settings.
     *
     * @return int The number of settings
     */
    public function count()
    {
        return count( $this->_settings );
    }

    protected function getCacheControlHeader()
    {
        $parts = array();
        ksort( $this->cacheControl );
        foreach ( $this->cacheControl as $key => $value )
        {
            if ( true === $value )
            {
                $parts[] = $key;
            }
            else
            {
                if ( preg_match( '#[^a-zA-Z0-9._-]#', $value ) )
                {
                    $value = '"' . $value . '"';
                }

                $parts[] = "$key=$value";
            }
        }

        return implode( ', ', $parts );
    }

    /**
     * Parses a Cache-Control HTTP header.
     *
     * @param string $header The value of the Cache-Control HTTP header
     *
     * @return array An array representing the attribute values
     */
    protected function parseCacheControl( $header )
    {
        $cacheControl = array();
        preg_match_all( '#([a-zA-Z][a-zA-Z_-]*)\s*(?:=(?:"([^"]*)"|([^ \t",;]*)))?#', $header, $matches, PREG_SET_ORDER );
        foreach ( $matches as $match )
        {
            $cacheControl[strtolower( $match[1] )] = isset( $match[3] ) ? $match[3] : ( isset( $match[2] ) ? $match[2] : true );
        }

        return $cacheControl;
    }
}
