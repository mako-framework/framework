<?php

namespace mako\tests\unit\classes\preload\classes;

class CG
{
	public function a(): CB
	{
		return new CB;
	}

	public function b(): string
	{
		return '';
	}
}
