<?php
declare(strict_types=1);


namespace Ludoi\Components\RecoverPassword;

interface RecoverPasswordControlFactory
{
	public function create(): RecoverPasswordControl;
}