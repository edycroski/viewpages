<?php

/**
 * Objects model config
 *
 * @link https://github.com/ddpro/admin/blob/master/docs/model-configuration.md
 */

return [

    'title' => 'Objects',

    'single' => 'object',

    'model' => '\Delatbabel\ViewPages\Models\Vobject',

    'server_side' => true,

    /**
     * The display columns
     */
    'columns'     => [
        'id'   => [
            'title' => 'ID',
        ],
        'objectkey' => [
            'title' => 'Object Key',
        ],
        'name'      => [
            'title' => 'Name',
        ],
    ],

    /**
     * The filter set
     */
    'filters'     => [
        'objectkey' => [
            'title' => 'Object Key',
        ],
        'name'      => [
            'title' => 'Name',
        ],
        'category'  => [
            'title'                 => 'Category',
            'type'                  => 'relationship',
            'name_field'            => 'name',
            'options_sort_field'    => 'name',
            'options_filter'        => '\Delatbabel\NestedCategories\Helpers\CategoryHelper::filterCategoriesByParentSlug',
            'options_filter_params' => ['object-types']
        ],
    ],

    /**
     * The editable fields
     */
    'edit_fields' => [
        'objectkey'   => [
            'title' => 'Object Key',
            'type'  => 'text',
        ],
        'name'        => [
            'title' => 'Name',
            'type'  => 'text',
        ],
        'description' => [
            'title' => 'Description',
            'type'  => 'text',
        ],
        'category'    => [
            'title'                 => 'Category <span class="text-danger">*</span>',
            'type'                  => 'relationship',
            'name_field'            => 'name',
            'options_sort_field'    => 'name',
            'options_filter'        => '\Delatbabel\NestedCategories\Helpers\CategoryHelper::filterCategoriesByParentSlug',
            'options_filter_params' => ['object-types']
        ],
        'websites'    => [
            'title'              => 'Websites',
            'type'               => 'relationship',
            'name_field'         => 'name',
            'options_sort_field' => 'name',
        ],
        'content'     => [
            'title' => 'Content',
            'type'  => 'wysiwyg',
            'limit' => 32000,
        ],
    ],

    'form_width' => 700,
];
