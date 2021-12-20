<?php

return [
    'products' => [
        'columns' => [
            'codes' => [
                'article_number'      => 'Article Number',
                'item_number'         => 'Item Number',
            ],
            'costing' => [
                'unit_price'            => 'Unit Price',
            ],
            'quantities' => [
                'cases'                 => 'Total Cartons',
                'total_unit_qty'        => 'Total Qty (Pcs)',
                'case_qty'              => 'Pieces per Carton',
                'case_inner_qty'        => 'Carton Inner Qty',
            ],
            'general' => [
                'name'                  => 'Product\'s Description',
                'product_description'   => 'Product\'s Details',
                'packing_instructions'  => 'Packing Instructions',
                'barcode'               => 'Barcode',
            ],
            'measurements' => [
                'case_width_m'        => 'Carton Width (m)',
                'case_height_m'       => 'Carton Height (m)',
                'case_depth_m'        => 'Carton Length (m)',
                'case_weight_kg'      => 'Carton Weight (kg)',
                'unit_width_m'        => 'Unit Width (m)',
                'unit_height_m'       => 'Unit Height (m)',
                'unit_depth_m'        => 'Unit Length (m)',
                'unit_weight_kg'      => 'Unit Weight (kg)',
            ]
        ]
    ]
];
