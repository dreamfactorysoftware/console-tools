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

use DreamFactory\Library\Console\Bags\GenericBag;
use DreamFactory\Library\Console\Interfaces\NodeLike;
use DreamFactory\Library\Console\Utility\CommandHelper;

/**
 * A bag to hold meta data
 */
class MetaDataBag extends GenericBag
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string My node ID
     */
    const NODE_ID = self::META_DATA_KEY;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param array $contents
     *
     * @return MetaDataBag
     */
    public function initialize( $contents = array() )
    {
        $this->_id = $this->_id ?: static::NODE_ID;

        if ( empty( $contents ) )
        {
            $contents = $this->getSchema();
        }

        parent::initialize( $contents );
    }

    /**
     * Adds comment to the metadata for this node
     *
     * @param string $comment
     *
     * @return $this
     */
    public function addComment( $comment )
    {
        return $this->set(
            'comments',
            array_merge(
                $this->get( 'comments', array() ),
                $this->formatComment( $comment )
            )
        );
    }

    /**
     * @param string $comment
     *
     * @return array
     */
    public function formatComment( $comment )
    {
        return array(CommandHelper::getCurrentTimestamp() => $comment);
    }

    /**
     * @param bool $addComment If true, a "created" comment is added to the schema
     *
     * @return array|NodeLike
     */
    public function getSchema( $addComment = true )
    {
        $_metadata = array(
            'comments'   => array(),
            'updated_at' => CommandHelper::getCurrentTimestamp(),
        );

        if ( $addComment )
        {
            $_metadata['comments'][] = $this->formatComment( 'Creation' );
        }

        return $_metadata;
    }
}
