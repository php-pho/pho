<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude(['vendor'])
    ->name('*.php');

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha', 'imports_order' => ['const', 'class', 'function']],
        'no_unused_imports' => true,
        'class_attributes_separation' => true,
        'blank_line_after_opening_tag' => true,
        'single_blank_line_before_namespace' => true,
        'single_line_after_imports' => true,
        'single_import_per_statement' => true,
        'single_blank_line_at_eof' => true,
        'single_class_element_per_statement' => true,
        'class_attributes_separation' => true,
        'new_with_braces' => true,
        'no_extra_blank_lines' => ['tokens' => ['use', 'use_trait']],
        'no_whitespace_in_blank_line' => true,
        'phpdoc_separation' => true,
        'concat_space' => ['spacing' => 'one'],
    ])
    ->setFinder($finder);
