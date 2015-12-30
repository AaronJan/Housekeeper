<?php
/**
 * Configure file for Housekeeper package.
 *
 * @author         AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package        Housekeeper
 * @version        2.0-dev
 * @license        Apache 2.0
 * @copyright  (c) 2015, AaronJan
 * @link           https://github.com/AaronJan/Housekeeper
 */

return [

    /*
     |--------------------------------------------------------------------------
     | Repository Directory
     |--------------------------------------------------------------------------
     |
     | Where to put repository class.
     |
     */

    'directory' => 'app/Repositories/',

    /*
     |--------------------------------------------------------------------------
     | Paginate Configures
     |--------------------------------------------------------------------------
     */

    'paginate' => [

        /*
         |--------------------------------------------------------------------------
         | Page Size
         |--------------------------------------------------------------------------
         |
         | How many entries per page for "paginate" method.
         |
         */

        'per_page' => 15,

    ],

    'abilities' => [

        /*
         |--------------------------------------------------------------------------
         | Cacheable
         |--------------------------------------------------------------------------
         |
         |
         |
         */

        'cache' => [

            /*
             |--------------------------------------------------------------------------
             | Cache Key Prefix
             |--------------------------------------------------------------------------
             |
             |
             |
             */

            'prefix' => 'housekeeper_',

        ],

    ],

];