<?php

/**
 * @file
 * Lists available colors and color schemes for the Bootstrap Barrio theme.
 */

$info = [
  // Available colors and color labels used in theme.
  'fields' => [
    'top' => t('Header background'),
    'bg' => t('Main background'),
    'sidebar' => t('Sidebar background'),
    'sidebarborders' => t('Sidebar borders'),
    'footer' => t('Footer background'),
    'button' => t('Primärfarbe'),
    'buttonhover' => t('Primärfarbe hover'),
	'secondary' => t('Sekundärfarbe'),
	'secondaryhover' => t('Sekundärfarbe hover'),
    'text' => t('Text color'),
    'link' => t('Link color'),
    
  ],
  // Pre-defined color schemes.
  'schemes' => [
    'default' => [
      'title' => t('Repanet (Standard)'),
      'colors' => [
        'top' => '#ffffff',
        'bg' => '#fefefe',
        'sidebar' => '#eeeeee',
        'sidebarborders' => '#cccccc',
        'footer' => '#eaeaea',
        'button' => '#0055a4',
        'buttonhover' => '#003382',
		'secondary' => '#76b729',
		'secondaryhover' => '#549507',
        'text' => '#333333',
        'link' => '#004493',
      ],
    ],
  ],

  // CSS files (excluding @import) to rewrite with new color scheme.
  'css' => [
    'css/colors.css',
  ],

  // Files to copy.
  'copy' => [
    'logo.svg',
  ],

  // Preview files.
  'preview_library' => 'repanet/color.preview',
  'preview_html' => 'color/preview.html',

  // Attachments.
  '#attached' => [
    'drupalSettings' => [
      'color' => [
        // Put the logo path into JavaScript for the live preview.
        'logo' => theme_get_setting('logo.url', 'bartik'),
      ],
    ],
  ],
];
