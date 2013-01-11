<?php

//---------------------------------------------
// Language file used by mako\Validate
//---------------------------------------------

return array
(
	'validate.required'                 => 'The %1$s field is required.',
	'validate.min_length'               => 'The value of the %1$s field must be at least %2$s characters long.',
	'validate.max_length'               => 'The value of the %1$s field must be less than %2$s characters long.',
	'validate.exact_length'             => 'The value of the %1$s field must be exactly %2$s characters long.',
	'validate.less_than'                => 'The value of the %1$s field must be less than %2$s.',
	'validate.less_than_or_equal_to'    => 'The value of the %1$s field must be less than or equal to %2$s.',
	'validate.greater_than'             => 'The value of the %1$s field must be greater than %2$s.',
	'validate.greater_than_or_equal_to' => 'The value of the %1$s field must be greater than or equal to %2$s.',
	'validate.between'                  => 'The value of the %1$s field must be between %2$s and %3$s.',
	'validate.match'                    => 'The values of the %1$s field and %2$s field must match.',
	'validate.different'                => 'The values of the %1$s field and %2$s field must be different.',
	'validate.regex'                    => 'The value of the %1$s field does not match the required format.',
	'validate.integer'                  => 'The %1$s field must contain an integer.',
	'validate.float'                    => 'The %1$s field must contain a float.',
	'validate.natural'                  => 'The %1$s field must contain a natural number.',
	'validate.natural_non_zero'         => 'The %1$s field must contain a non zero natural number.',
	'validate.hex'                      => 'The %1$s field must contain a valid hexadecimal value.',
	'validate.alpha'                    => 'The %1$s field must contain only letters.',
	'validate.alpha_unicode'            => 'The %1$s field must contain only letters.',
	'validate.alphanumeric'             => 'The %1$s field must contain only letters and numbers.',
	'validate.alphanumeric_unicode'     => 'The %1$s field must contain only letters and numbers.',
	'validate.alpha_dash'               => 'The %1$s field must contain only numbers, letters and dashes.',
	'validate.alpha_dash_unicode'       => 'The %1$s field must contain only numbers, letters and dashes.',
	'validate.email'                    => 'The %1$s field must contain a valid e-mail address.',
	'validate.email_domain'             => 'The %1$s field must contain a valid e-mail address.',
	'validate.url'                      => 'The %1$s field must contain a valid URL.',
	'validate.ip'                       => 'The %1$s field must contain a valid IP address.',
	'validate.in'                       => 'The %1$s field must contain one of available options.',
	'validate.not_in'                   => 'The %1$s field contains an invalid value.',
	'validate.date'                     => 'The %1$s field must contain a valid date.',
	'validate.before'                   => 'The %1$s field must contain a date before %3$s.',
	'validate.after'                    => 'The %1$s field must contain a date after %3$s.',
	'validate.token'                    => 'Invalid security token.',
	'validate.uuid'                     => 'Invalid UUID.',
	'validate.unique'                   => 'The %1$s must be unique.',
	'validate.exists'                   => 'The %1$s doesn\'t exist.',
);

/** -------------------- End of file --------------------**/