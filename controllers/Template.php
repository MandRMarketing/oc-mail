<?php namespace Mandr\Mail\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use System\Models\MailTemplate;

/**
 * Back-end Controller
 */
class Template extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = ['mandr.mail.template'];

    /**
     * Ensure that by default our menu sidebar is active
     */
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Mandr.Mail', 'mail', 'template');
    }

    public function index()
    {
        MailTemplate::syncAll();
        $this->asExtension('ListController')->index();
    }

    public function stats($recordId = null, $context = null)
    {
        $this->asExtension('FormController')->preview($recordId, 'stats');

        $template = MailTemplate::findOrFail($recordId);
        $this->vars['lastTs'] = end($this->vars['lastWeek'])->ts;
    }
}
