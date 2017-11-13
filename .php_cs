<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return Config::create()
->setRules
([
	'no_trailing_whitespace' => true,
	'no_whitespace_in_blank_line' => true,
	'encoding' => true,
	'single_blank_line_at_eof' => true,
	'elseif' => true,
	'blank_line_after_namespace' => true,
	'blank_line_after_opening_tag' => true,
	'cast_spaces' => true,
	'hash_to_slash_comment' => true,
	'lowercase_cast' => true,
	'lowercase_constants' => true,
	'lowercase_keywords' => true,
	'no_alias_functions' => true,
	'no_extra_consecutive_blank_lines' => true,
	'no_leading_import_slash' => true,
	'standardize_not_equals' => true,
	'method_argument_space' => true,
	'linebreak_after_opening_tag' => true,
	'no_blank_lines_after_phpdoc' => true,
	'no_leading_namespace_whitespace' => true,
	'single_blank_line_before_namespace' => true,
	'native_function_casing' => true,
	'no_closing_tag' => true,
	'no_singleline_whitespace_before_semicolons' => true,
	'no_spaces_inside_parenthesis' => true,
	'no_trailing_comma_in_list_call' => true,
	'no_trailing_comma_in_singleline_array' => true,
	'no_trailing_whitespace_in_comment' => true,
	'no_whitespace_before_comma_in_array' => true,
	'object_operator_without_whitespace' => true,
	'phpdoc_align' => true,
	'whitespace_after_comma_in_array' => true,
	'trailing_comma_in_multiline_array' => true,
])
->setRiskyAllowed(true)
->setFinder(Finder::create()->in(__DIR__));
