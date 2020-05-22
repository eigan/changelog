<?php

/*
 * This document has been generated with
 * https://mlocati.github.io/php-cs-fixer-configurator/#version:2.16.3|configurator
 * you can change this configuration by importing this file.
 */
return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony:risky' => true,
        '@Symfony' => true,
        '@PSR2' => true,
        '@PSR1' => true,
        '@PHP73Migration' => true,
        '@PHP71Migration:risky' => true,
        '@PHP71Migration' => true,
        '@PHP70Migration:risky' => true,
        '@PHP70Migration' => true,
        'php_unit_dedicate_assert' => true,
        'php_unit_dedicate_assert_internal_type' => true,
        'array_indentation' => true,
        'fully_qualified_strict_types' => true,
        'final_internal_class' => true,
        'explicit_string_variable' => true,
        'method_chaining_indentation' => true,
        'no_unset_on_property' => true,
        'php_unit_method_casing' => true,
        'php_unit_no_expectation_annotation' => true,
        'php_unit_set_up_tear_down_visibility' => true,
        'php_unit_test_case_static_method_calls' => true,
        'phpdoc_order' => true,
        'phpdoc_var_annotation_correct_order' => true,
        'protected_to_private' => true,
        'simple_to_complex_string_variable' => true,
        'strict_param' => true,
    ])
    ->setFinder(PhpCsFixer\Finder::create()
        ->exclude('vendor')
        ->in([__DIR__ . '/src', __DIR__ . '/bin', __DIR__ . '/tests'])
    )
;
