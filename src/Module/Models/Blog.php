<?php

namespace RefinedDigital\Blog\Module\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use RefinedDigital\CMS\Modules\Core\Models\CoreModel;
use RefinedDigital\CMS\Modules\Core\Traits\IsArticle;
use RefinedDigital\CMS\Modules\Pages\Traits\IsPage;
use RefinedDigital\CMS\Modules\Tags\Traits\Taggable;
use RefinedDigital\CMS\Modules\Pages\Traits\HasContentBlocks;

class Blog extends CoreModel
{
    use SoftDeletes;
    use IsPage;
    use Taggable;
    use IsArticle;
    use HasContentBlocks;

    protected $order = ['column' => 'published_at', 'direction' => 'desc'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at', 'published_at'];

    protected $fillable = [
        'published_at',
        'active',
        'position',
        'name',
        'image',
        'banner',
        'text',
        'data',
        'external_link',
        'file',
        'images',
        'featured'
    ];

    protected $appends = ['excerpt', 'modelImages'];

    protected $hidden = ['taggables'];

    protected $casts = [
        'data'   => 'object',
        'images' => 'object'
    ];

    /**
     * The fields to be displayed for creating / editing
     *
     * @var array
     */
    public $formFields = [
        [
            'name'     => 'Content',
            'sections' => [
                'left'  => [
                    'blocks' => [
                        [
                            'name'   => 'Content',
                            'fields' => [
                                [
                                    [ 'label' => 'Heading', 'name' => 'name', 'required' => true, 'attrs' => ['v-model' => 'content.name', '@keyup' => 'updateSlug' ] ],
                                    [ 'label' => 'Date', 'name' => 'published_at','required' => true, 'type' => 'datetime' ],
                                ],
                            ]
                        ]
                    ]
                ],
                'right' => [
                    'blocks' => [
                        [
                            'name'   => 'Settings',
                            'fields' => [
                                [
                                    ['label' => 'Active', 'name' => 'active', 'required' => true, 'type'  => 'select', 'options' => [1 => 'Yes', 0 => 'No'] ],
                                ],
                            ]
                        ],
                    ]
                ]
            ]
        ],
    ];

    protected $contentBlocks = [
        'name' => 'Content Blocks',
        'fields' => [
            [
                ['label' => 'Content', 'name' => 'content', 'type' => 'contentBlocks', 'hideLabel' => true],
            ],
        ],
    ];

    protected $blockFeatured = [
        'label'    => 'Featured',
        'name'     => 'featured',
        'required' => false,
        'type'     => 'select',
        'options'  => [0 => 'No', 1 => 'Yes']
    ];

    protected $blockTags = [
        'name'   => 'Tags',
        'fields' => [
            [
                [ 'label' => 'Tags', 'name' => 'tags', 'type' => 'tags', 'hideLabel' => true,  'tagType' => 'tags' ],
            ]
        ]
    ];

    protected $blockCategories = [
        'name'   => 'Categories',
        'fields' => [
            [
                [ 'label' => 'Categories', 'name' => 'categories', 'type' => 'tags', 'hideLabel' => true, 'tagType' => 'categories' ],
            ]
        ]
    ];

    protected $blockExternalLink = [
        'name'   => 'External Link',
        'fields' => [
            [
                [ 'label' => 'External Link', 'name' => 'external_link', 'required' => true, 'hideLabel' => true, ],
            ]
        ]
    ];

    protected $blockFile = [
        'name'   => 'File',
        'fields' => [
            [
                [ 'label' => 'File', 'name' => 'file', 'required' => true, 'hideLabel' => true, 'type'  => 'file' ],
            ]
        ]
    ];

    protected $imagesBlock = [
        'name'   => 'Images',
        'fields' => [
            [
                [
                    'label'    => 'Images', 'name' => 'images', 'type' => 'repeatable', 'required' => false, 'hideLabel' => true,
                    'fields' => [
                        [ 'name' => 'Image', 'page_content_type_id' => 4, 'field' => 'image', 'note' => 'Image will be resized to <strong>Fit <em>within</em> 1600px wide x 1280px tall</strong>' ],
                    ]
                ],
            ],
        ]
    ];

    protected $thumbnailImage = [
        ['label' => 'Thumbnail', 'name' => 'image', 'required'  => true, 'hideLabel' => false, 'type' => 'image' ]
    ];

    public function __construct(array $attributes = [])
    {
        $config = config('blog');

        if (isset($config['excerptLength']) && $config['excerptLength']) {
            $this->excerptLength = $config['excerptLength'];
        }
        if (isset($config['excerptType']) && $config['excerptType']) {
            $this->excerptType = $config['excerptType'];
        }

        return parent::__construct($attributes);
    }

    public function scopePublished($query)
    {
        $now = Carbon::now()->setTimezone(config('blog.timezone'));
        $query->where('published_at', '<=', $now);
    }

    public function scopeFeatured($query)
    {
        $query->whereFeatured(1);
    }

    public function scopeNotFeatured($query)
    {
        $query->whereFeatured(0);
    }

    public function setFormFields()
    {
        $config      = config('blog');
        $fields      = $this->formFields;
        $rightBlocks = $fields[0]['sections']['right']['blocks'];

        if(isset($config['featured']) && $config['featured']) {
            array_splice($fields[0]['sections']['right']['blocks'][0]['fields'][0], 1, 0,
                [$this->blockFeatured]);
        }

        if(isset($config['categories']) && $config['categories']) {
            array_splice($fields[0]['sections']['right']['blocks'], 1, 0, [$this->blockCategories]);
        }
        if(isset($config['tags']) && $config['tags']) {
            array_splice($fields[0]['sections']['right']['blocks'], 1, 0, [$this->blockTags]);
        }

        if((isset($config['external_link']) && $config['external_link']) || (isset($config['externalLink']) && $config['externalLink'])) {
            $link = $config['external_link'] ?? $config['externalLink'];
            $show = true;
            if(is_array($link)) {
                if(isset($link['enable']) && !$link['enable']) {
                    $show = false;
                }
                if(isset($link['showLabel']) && $link['showLabel']) {
                    $this->blockExternalLink['fields'][0][0]['hideLabel'] = false;
                }
                if(isset($link['label']) && $link['label']) {
                    $this->blockExternalLink['fields'][0][0]['label'] = $link['label'];
                }
            }

            if($show) {
                $index = sizeof($rightBlocks);
                array_splice($fields[0]['sections']['right']['blocks'], $index, 0,
                    [$this->blockExternalLink]);
            }
        }

        if(isset($config['file']) && $config['file']) {
            $index = sizeof($rightBlocks);
            array_splice($fields[0]['sections']['right']['blocks'], $index, 0, [$this->blockFile]);
        }

        if(isset($config['images']) && $config['images']) {
            $imageBlock = $this->imagesBlock;
            if(is_array($config['images'])) {
                $imageBlock['fields'] = $config['images'];
            }
            $fields[] = $imageBlock;
        }

        if (isset($config['contentBlocks']) && $config['contentBlocks']) {
            $fields[] = $this->contentBlocks;
        }

        return $this->setImages($fields, $config);
    }

    public function getModelImagesAttribute()
    {
        if($this->attributes['images']) {
            $decode = json_decode($this->attributes['images']);

            if(!is_array($decode)) {
                return json_decode($decode);
            }

            return $decode;
        }

        return [];
    }

    private function setImageAttributes($field, $config)
    {
        $field[0]['imageNote'] = 'Image here will be resized to <strong><em>FIT WITHIN</em> '.$config['width'].'px x '.$config['height'].'px</strong>';


        if ($config['required']) {
            $field[0]['required'] = $config['required'];
        }

        return $field;
    }

    private function setImages($fields, $config)
    {
        $group = [
            'name'   => 'Images',
            'fields' => [ ]
        ];

        if (isset($config['thumbnail'], $config['thumbnail']['show']) && $config['thumbnail']['show']) {
            $fieldData = $this->setImageAttributes($this->thumbnailImage, $config['thumbnail']);

            if ($config['featured'] && isset($config['thumbnail']['featured'])) {
                $fieldData[0]['imageNote'] .= '<br/>For featured post: Image here will be resized to <strong><em>FIT WITHIN</em> '.$config['thumbnail']['featured']['width'].'px x '.$config['thumbnail']['featured']['height'].'px</strong>';
            }

            $group['fields'][] = $fieldData;

        }

        if (sizeof($group['fields'])) {
            array_splice($fields[0]['sections']['right']['blocks'], 1, 0, [$group]);
        }

        return $fields;
    }
}
