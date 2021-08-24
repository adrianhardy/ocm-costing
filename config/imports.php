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
                'cases'                 => 'Total Cartons',
                'total_unit_qty'        => 'Total Qty (Pcs)',
                'case_qty'              => 'Pieces per Carton',
                'case_inner_qty'        => 'Case Inner Qty',
            ],
            'general' => [
                'name'                  => 'Product\'s Description',
                'packing_instructions'  => 'Packing Instructions',
                'barcode'               => 'Barcode',
            ],
            'volume' => [
                'case_volume_cu_m'      => 'cu M per Carton',
                'case_volume_cu_ft'     => 'cu Ft per Carton',
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
