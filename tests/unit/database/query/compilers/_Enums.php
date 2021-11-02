<?php

namespace mako\tests\unit\database\query\compilers;

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
