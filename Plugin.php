<?php namespace Mandr\Mail;

use Backend;
use Event;
use Mail;
use Mandr\Mail\Models\Email;
use Mandr\Mail\Controllers\Mail as MailController;
use System\Classes\PluginBase;
use System\Models\MailTemplate;

/**
 * Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'mandr.mail::lang.plugin_name',
            'description' => 'mandr.mail::lang.plugin_description',
            'author'      => 'Matiss Janis Aboltins, Will Hawthorne @ M&R Marketing Group',
            'homepage'    => 'http://mja.lv/',
            'icon'        => 'icon-envelope'
        ];
    }

    public function registerNavigation()
    {
        return [
            'mail' => [
                'label'       => 'mandr.mail::lang.controllers.mail.title',
                'url'         => Backend::url('mandr/mail/mail'),
                'icon'        => 'icon-paper-plane-o',
                'permissions' => ['mandr.mail.*'],
                'order'       => 500,
            ]
        ];
    }

    public function registerFormWidgets()
    {
        return [
            'Mandr\Mail\FormWidgets\EmailGrid' => [
                'label' => 'mandr.mail::lang.formwidget.title',
                'code'  => 'emailgrid'
            ]
        ];
    }

    public function registerPermissions()
    {
        return [
            'mandr.mail.template' => ['tab' => 'mandr.mail::lang.controllers.mail.title', 'label' => 'mandr.mail::lang.permission.template'],
            'mandr.mail.mail'     => ['tab' => 'mandr.mail::lang.controllers.mail.title', 'label' => 'mandr.mail::lang.permission.mail']
       ];
    }

    /**
     * Attach event listeners on boot.
     * @return void
     */
    public function boot()
    {
        // Before send: attach blank image that will track mail opens
        Event::listen('mailer.prepareSend', function($self, $view, $message) {
            $swift = $message->getSwiftMessage();

            $mail = Email::create([
                'code' => $view,
                'to' => $swift->getTo(),
                'cc' => $swift->getCc(),
                'bcc' => $swift->getBcc(),
                'subject' => $swift->getSubject(),
                'body' => $swift->getBody(),
                'sender' => $swift->getSender(),
                'reply_to' => $swift->getReplyTo(),
                'date' => $swift->getDate()
            ]);
        });

        // After send: log the result
        Event::listen('mailer.send', function($self, $view, $message, $response) {
            $swift = $message->getSwiftMessage();

            $mail = Email::where('code', $view)
                 ->get()
                 ->last();

            if ($mail === null) return;

            $mail->response = $response;
            $mail->sent = true;
            $mail->save();
        });

        // Use for the mails sent list filter
        Event::listen('backend.filter.extendScopesBefore', function ($filter) {
            if (! ($filter->getController() instanceof MailController)) {
                return;
            }

            $filter->scopes['views']['options'] = [];

            $templates = MailTemplate::get();
            foreach ($templates as $template) {
                $filter->scopes['views']['options'][$template->code] = $template->code;
            }
        });

        // Extend the mail template so that we could have the number of sent emails
        // in the template list of this plugin.
        MailTemplate::extend(function($model) {

            // Email relation
            $model->addDynamicMethod('emails', function() use ($model) {
                return $model->hasMany('Mandr\Mail\Models\Email', 'code', 'code');
            });

            // Emails sent
            $model->addDynamicMethod('getTimesSentAttribute', function() use ($model) {
                return (int) $model->emails()->count();
            });

            // Last time sent
            $model->addDynamicMethod('getLastSentAttribute', function() use ($model) {
                $email = $model->emails()->orderBy('id', 'desc')->first();
                return $email ? $email->created_at : null;
            });

        });
    }
}
