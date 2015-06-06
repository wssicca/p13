<?php

/*
 * This file is part of the P13 package.
 * 
 * (c) Wagner Sicca <wssicca@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace p13\core\config;

/**
 * Description of AbstractConfiguration
 *
 * @author Wagner Sicca <wssicca@gmail.com>
 * @namespace p13\core\config
 * @package p13\core\config
 * @abstract
 */
abstract class AbstractConfiguration implements ConfigurationInterface
{

    /**
     *
     * @var AbstractConfiguration
     */
    protected static $instance;

    protected function __construct()
    {
        
    }

}
