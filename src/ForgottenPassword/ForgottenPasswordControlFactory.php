<?php
declare(strict_types=1);


namespace Ludoi\Components\ForgottenPassword;

interface ForgottenPasswordControlFactory
{
	public function create(): ForgottenPasswordControl;
}