<?php
declare(strict_types=1);


namespace Ludoi\Components\Login;

interface LoginControlFactory
{
	public function create(): LoginControl;
}