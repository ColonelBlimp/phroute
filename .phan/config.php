<?php

return [
    'directory_list' => [
        'src',
        'vendor/'
    ],

    'exclude_analysis_directory_list' => [
        'vendor/',
        'phpunit/'
    ],

    'target_php_version' => '7.2',

    'backward_compatibility_checks' => true,

    'quick_mode' => false,

    'analyze_signature_compatibility' => true,

    'minimum_severity' => 0,

    'allow_missing_properties' => false,

    'null_casts_as_any_type' => false,

    'array_casts_as_null' => false,

    'scalar_implicit_cast' => false,

    'scalar_implicit_partial' => [],

    'ignore_undeclared_variables_in_global_scope' => false,

    'suppress_issue_types' => [
        // 'PhanUndeclaredTypeParameter'
        // 'PhanUndeclaredMethod',
    ],

    'whitelist_issue_types' => [
        // 'PhanAccessMethodPrivate',
    ],

    'plugins' => [
        'AlwaysReturnPlugin',
        'UnreachableCodePlugin',
        'DollarDollarPlugin',
        'DuplicateArrayKeyPlugin',
        'PregRegexCheckerPlugin',
        'PrintfCheckerPlugin',
    ]
];
