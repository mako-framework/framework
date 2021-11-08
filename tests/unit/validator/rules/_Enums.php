<?php

namespace mako\tests\unit\validator\rules;

enum FooEnum
{
	case ONE;
	case TWO;
}

enum BarEnum: int
{
	case ONE = 1;
	case TWO = 2;
}
