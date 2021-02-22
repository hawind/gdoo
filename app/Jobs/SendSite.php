<?php namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Gdoo\Index\Services\NotificationService;

class SendSite implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $users;
    protected $subject;
    protected $content;
    protected $url;
    protected $auth;

    public function __construct($users, $subject, $content = '', $url = '')
    {
        $this->users = $users;
        $this->subject = $subject;
        $this->content = $content;
        $this->url = $url;
    }

    public function handle()
    {
        $users = $this->users;
        $subject = $this->subject;
        $content = $this->content;
        $url = $this->url;
        return NotificationService::site($users, $subject, $content, $url);
    }
}
