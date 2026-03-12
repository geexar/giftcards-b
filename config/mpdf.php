<?php

return [
    'options' => [
        'mode' => 'utf-8',
        'format' => 'A4',
        'default_font_size' => 10,
        'margin_top' => 10,
        'margin_bottom' => 10,
        'margin_left' => 10,
        'margin_right' => 10,

        // 1. Set the default font name
        'default_font' => 'arial',

        // 2. Define where your font files are located
        'fontDir' => array_merge((new \Mpdf\Config\ConfigVariables())->getDefaults()['fontDir'], [
            public_path('assets/fonts')
        ]),

        // 3. Map the font name to the actual files
        'fontdata' => array_merge((new \Mpdf\Config\FontVariables())->getDefaults()['fontdata'], [
            'arial' => [
                'R'  => 'arial.ttf',    // Regular
                'B'  => 'arialbd.ttf',  // Bold
                'I'  => 'ariali.ttf',   // Italic
                'BI' => 'arialbi.ttf',  // Bold Italic,
                'useOTL'     => 0xFF,   // <--- THIS FIXES SEPARATED LETTERS
                'useKashida' => 75,     // <--- THIS IMPROVES ARABIC SPACING
            ],
        ]),
    ]
];
