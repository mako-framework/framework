<?php

//---------------------------------------------
// ru_RU inflection rules
//---------------------------------------------

return array
(
	// Inflection rules

	'rules' => array
	(
		// Plural noun forms

		'plural' => array
		(

		),

		// Irregular words

		'irregular' => array
		(
			// '1' => '...', '1' => array('0', '2') or '1' => array('0', '2', '...')

			'минута'  => array('минут', 'минуты', 'минуты'),
			'час'     => array('часов', 'часа', 'часы'),
			'день'    => array('дней', 'дня', 'дни'),
			'неделя'  => array('недель', 'недели', 'недели'),
			'символ'  => array('символов', 'символа', 'символы'),
			'символа' => array('символов', 'символов'), // genitive
		),
	),

	// Pluralization function

	'pluralize' => function($word, $count, $rules)
	{
		if($count !== 1)
		{
			if(isset($rules['irregular'][$word]))
			{
				if(is_array($pluralized = $rules['irregular'][$word]))
				{
					if($count === null || $count === false)
					{
						return isset($pluralized[2]) ? $pluralized[2]  : $word;
					}

					if(!isset($pluralized[0], $pluralized[1]))
					{
						return $word;
					}

					if(is_float($count))
					{
						return $pluralized[0];
					}

					$count = abs($count);

					return $count % 10 == 1 && $count % 100 != 11 ? $word :
						($count % 10 >= 2 && $count % 10 <= 4 && ($count % 100 < 10 || $count % 100 >= 20) ? $pluralized[1] : $pluralized[0]);
				}

				return $pluralized;
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