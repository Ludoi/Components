<?php
declare(strict_types=1);


namespace Ludoi\Components\RecoverPassword;

use Ludoi\Utils\Users\Users;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use Nextras\FormsRendering\Renderers\FormLayout;

class RecoverPasswordControl extends Control
{
	private Users $users;

	public function __construct(Users $users)
	{
		$this->users = $users;
	}

	protected function createComponentForgottenForm(): Form
	{
		$form = new Form;
		$form->addHidden('username', $this->username);
		$form->addPassword('passwordOne', 'Nové heslo:')->setRequired();
		$form->addPassword('passwordRepeat', 'Zopakovat heslo:')->setRequired();

		$form->addSubmit('send', 'Odeslat');

		$form->onSuccess[] = [$this, 'recoverFormSubmitted'];

		$form->setRenderer(new Bs4FormRenderer(FormLayout::VERTICAL));

		return $form;
	}

	public function recoverFormSubmitted(Form $form): void
	{
		try {
			$values = $form->getValues();
			if (is_null($user = $this->users->getUser($values->username))) {
				$this->flashMessage('Uživatel nenalezen', 'danger');
			} else if (!$user->active) {
				$this->flashMessage('Uživatel nenalezen', 'danger');
			} else if ($values->passwordOne <> $values->passwordRepeat) {
				$this->flashMessage('Hesla nejsou stejná', 'danger');
			} else {
				$this->users->setPasswordInRow($user, $values->passwordOne);
				$this->flashMessage('Heslo změněno', 'success');
			}
			$this->redirect(':Front:Homepage:');
		} catch (AuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	}

	public function render(): void
	{
		$this->template->render(__DIR__ . '/RecoverPasswordControl.latte');
	}
}