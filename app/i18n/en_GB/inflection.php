<?php

//---------------------------------------------
// en_GB inflection rules
//---------------------------------------------

return array
(
	// Inflection rules

	'rules' => array
	(
		// Plural noun forms

		'plural' => array
		(
			'/(quiz)$/i'                     => "$1zes",
			'/^(ox)$/i'                      => "$1en",
			'/([m|l])ouse$/i'                => "$1ice",
			'/(matr|vert|ind)ix|ex$/i'       => "$1ices",
			'/(x|ch|ss|sh)$/i'               => "$1es",
			'/([^aeiouy]|qu)y$/i'            => "$1ies",
			'/(hive)$/i'                     => "$1s",
			'/(?:([^f])fe|([lr])f)$/i'       => "$1$2ves",
			'/(shea|lea|loa|thie)f$/i'       => "$1ves",
			'/sis$/i'                        => "ses",
			'/([ti])um$/i'                   => "$1a",
			'/(tomat|potat|ech|her|vet)o$/i' => "$1oes",
			'/(bu)s$/i'                      => "$1ses",
			'/(alias)$/i'                    => "$1es",
			'/(octop)us$/i'                  => "$1i",
			'/(ax|test)is$/i'                => "$1es",
			'/(us)$/i'                       => "$1es",
			'/s$/i'                          => "s",
			'/$/'                            => "s",
		),

		// Irregular words

		'irregular' => array
		(
			'audio'       => 'audio',
			'child'       => 'children',
			'deer'        => 'deer',
			'equipment'   => 'equipment',
			'fish'        => 'fish',
			'foot'        => 'feet',
			'goose'       => 'geese',
			'gold'        => 'gold',
			'information' => 'information',
			'man'         => 'men',
			'money'       => 'money',
			'police'      => 'police',
			'series'      => 'series',
			'sex'         => 'sexes',
			'sheep'       => 'sheep',
			'species'     => 'species',
			'tooth'       => 'teeth',
			'woman'       => 'women',
		),
	),

	// Pluralization function

	'pluralize' => function($word, $count, $rules)
	{
		if($count !== 1)
		{
			if(isset($rules['irregular'][$word]))
			{
				$word = $rules['irregular'][$word];
			}
			else
			{
				foreach($rules['plural'] as $search => $replace)
				{
					if(preg_match($search, $word))
					{
						$word = preg_replace($search, $replace, $word);

						break;
					}
				}
			}
		}

		return $word;
	},
);

/** -------------------- End of file --------------------**/