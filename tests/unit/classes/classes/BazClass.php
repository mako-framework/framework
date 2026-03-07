<?php

namespace mako\tests\unit\classes\classes;

use AllowDynamicProperties;

#[AllowDynamicProperties]
class BazClass
{
	use FooTrait;
}
