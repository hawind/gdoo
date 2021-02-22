<?php namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Gdoo\Index\Services\NotificationService;
use Mail;
use DB;
use Gdoo\User\Models\User;
use Gdoo\System\Models\Setting;
use Exception;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $users;
    protected $subject;
    protected $content;
    protected $view;

    public function __construct($view, $users, $subject, $content = '')
    {
        $this->users = $users;
        $this->subject = $subject;
        $this->content = $content;
        $this->view = $view;
    }

    public function handle()
    {
        $users = $this->users;
        $subject = $this->subject;
        $content = $this->content;
        $view = $this->view;
        return NotificationService::mail($view, $users, $subject, $content);
    }
}