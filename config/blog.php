<?php

return [
    'details_template_id' => '__ID__',

    'timezone' => 'UTC',

    'baseUrl' => 'blog',

    'thumbnail' => [
        'show' => true,
        'required' => true,
        'width' => 770,
        'height' => 400,
    ],

    'featured' => false,

    'tags' => false,

    'categories' => false,

    'file' => false,

    'contentBlocks' => true,

    'externalLink' => false,
    /**
     * or
     * 'externalLink' => [
     *   'enable' => true,
     *   'showLabel' => true,
     *   'label' => 'Link'
     * ]
     */
    'excerptLength' => 200,
    'excerptType' => 'character',

    'images' => false,

    /*
     * or an array of fields to use instead
    'images' => [
      [
          [ 'label' => 'Images', 'name' => 'images', 'type' => 'repeatable', 'required' => false, 'hideLabel' => true, 'fields' =>
              [
                  [ 'name' => 'Image', 'page_content_type_id' => 4, 'field' => 'image' ],
              ]
          ],
      ],
    */

];
