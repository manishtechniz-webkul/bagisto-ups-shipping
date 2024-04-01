<?php

return [
    [
        'key'    => 'sales.carriers.ups',
        'name'   => 'ups::app.admin.system.ups',
        'info'   => 'ups::app.admin.system.ups-description',
        'sort'   => 4,
        'fields' => [
            [
                'name'          => 'title',
                'title'         => 'ups::app.admin.system.title',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'description',
                'title'         => 'ups::app.admin.system.description',
                'type'          => 'textarea',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'  => 'ups_active',
                'title' => 'ups::app.admin.system.status',
                'type'  => 'boolean',
            ], [
                'name'       => 'mode',
                'title'      => 'ups::app.admin.system.mode',
                'type'       => 'select',
                'validation' => 'required',
                'options'    => [
                    [
                        'title' => 'ups::app.admin.system.development',
                        'value' => 'DEVELOPMENT',
                    ], [
                        'title' => 'ups::app.admin.system.live',
                        'value' => "LIVE",
                    ],
                ],
            ], [
                'name'          => 'access_license_key',
                'title'         => 'ups::app.admin.system.access-license-number',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'user_id',
                'title'         => 'ups::app.admin.system.user-id',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'password',
                'title'         => 'ups::app.admin.system.password',
                'type'          => 'password',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'shipper_number',
                'title'         => 'ups::app.admin.system.shipper',
                'type'          => 'text',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'container',
                'title'         => 'ups::app.admin.system.container',
                'type'          => 'select',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
                'options'       => [
                    [
                        'title' => 'ups::app.admin.system.package',
                        'value' => '02',
                    ], [
                        'title' => 'ups::app.admin.system.ups-letter',
                        'value' => '01'
                    ], [
                        'title' => 'ups::app.admin.system.ups-tube',
                        'value' => '03'
                    ], [
                        'title' => 'ups::app.admin.system.ups-pak',
                        'value' => '04'
                    ], [
                        'title' => 'ups::app.admin.system.ups-express-box',
                        'value' => '21',
                    ],
                ],
            ], [
                'name'          => 'weight_unit',
                'title'         => 'ups::app.admin.system.weight-unit',
                'type'          => 'select',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
                'options'       => [
                    [
                        'title' => 'ups::app.admin.system.lbs',
                        'value' => 'LBS'
                    ], [
                        'title' => 'ups::app.admin.system.kgs',
                        'value' => 'KGS',
                    ],
                ],
            ], [
                'name'          => 'services',
                'title'         => 'ups::app.admin.system.allowed-methods',
                'type'          => 'multiselect',
                'channel_based' => false,
                'locale_based'  => true,
                'options'       => [
                    [
                        'title' => 'ups::app.admin.system.next-day-air-early-am',
                        'value' => '14',
                    ], [
                        'title' => 'ups::app.admin.system.next-day-air',
                        'value' => '01',
                    ], [
                        'title' => 'ups::app.admin.system.next-day-air-saver',
                        'value' => '13',
                    ], [
                        'title' => 'ups::app.admin.system.2nd-day-air-am',
                        'value' => '59',
                    ], [
                        'title' => 'ups::app.admin.system.2nd-day-air',
                        'value' => '02',
                    ], [
                        'title' => 'ups::app.admin.system.3-day-select',
                        'value' => '12',
                    ], [
                        'title' => 'ups::app.admin.system.ups-ground',
                        'value' => '03',
                    ], [
                        'title' => 'ups::app.admin.system.ups-worldwide-express',
                        'value' => '07',
                    ], [
                        'title' => 'ups::app.admin.system.ups-worldwide-express-plus',
                        'value' => '54',
                    ], [
                        'title' => 'ups::app.admin.system.ups-worldwide-expedited',
                        'value' => '08',
                    ], [
                        'title' => 'ups::app.admin.system.ups-worldwide-saver',
                        'value' => '65',
                    ],
                ],
            ],
        ],
    ],
];