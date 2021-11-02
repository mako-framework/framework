<?php

namespace mako\tests\unit\database\query\compilers;

enum Foo
{
	case ONE;
	case TWO;
}

enum Bar: int
{
	case ONE = 1;
	case TWO = 2;
}
