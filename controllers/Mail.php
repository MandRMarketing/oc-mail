<?php namespace Mandr\Mail\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Mandr\Mail\Models\Email;

/**
 * Back-end Controller
 */
class Mail extends Controller
{
    public $hide_hints = false;

    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = ['mandr.mail.mail'];

    public $bodyClass = 'compact-container';

    /**
     * Ensure that by default our menu sidebar is active
     */
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Mandr.Mail', 'mail', 'mail');
    }

    public function index()
    {
        $this->vars['sent'] = Email::whereSent(true)->count();
        $this->vars['emails'] = Email::select('code')->groupBy('code')->get();

        $this->asExtension('ListController')->index();
    }
}
