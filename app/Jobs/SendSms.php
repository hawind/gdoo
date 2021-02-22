<?php namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Gdoo\Index\Services\NotificationService;

class SendSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $users;
    protected $subject;
    protected $content;

    public function __construct($users, $subject, $content = '')
    {
        $this->users = $users;
        $this->subject = $subject;
        $this->content = $content;
    }

    public function handle()
    {
        $users = $this->users;
        $subject = $this->subject;
        $content = $this->content;
        return NotificationService::sms($users, $subject, $content);
    }
}
