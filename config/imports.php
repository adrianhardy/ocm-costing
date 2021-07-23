<?php

return [
    'products' => [
        'columns' => [
            'codes' => [
                'article_number'      => 'Article Number',
                'item_number'         => 'Item Number',
            ],
            'costing' => [
                'unit_price'          => 'Unit Price',
            ],
            'quantities' => [
                'cases'                 => 'Total Cases',
                'total_unit_qty'        => 'Total Units (Qty)',
                'case_qty'              => 'Case Qty (units per case)',
                'case_inner_qty'        => 'Case Inner Qty',
            ],
            'general' => [
                'name'                  => 'Name',
                'packing_instructions'  => 'Packing Instructions',
                'barcode'               => 'Barcode',
            ],
            'volume' => [
                'case_volume_cu_m'      => 'Case Vol. cu M',
                'case_volume_cu_ft'     => 'Case Vol. cu Ft',
            ],
            'measurements' => [
                'case_width_m'        => 'Case Width (m)',
                'case_height_m'       => 'Case Width (m)',
                'case_depth_m'         => 'Case Depth (m)',
                'case_weight_kg'      => 'Case Weight (kg)',
            ]
        ]
    ]
];
