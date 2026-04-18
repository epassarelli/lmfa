<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\NewsletterSubscriber;
use App\Mail\WeeklyNewsletterMail;
use Illuminate\Support\Facades\Mail;

class SendNewsletterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $subscriber;
    public $newsData;

    public function __construct(NewsletterSubscriber $subscriber, array $newsData)
    {
        $this->subscriber = $subscriber;
        $this->newsData = $newsData;
    }

    public function handle(): void
    {
        if ($this->subscriber->status !== 'active') {
            return;
        }

        Mail::to($this->subscriber->email)->send(new WeeklyNewsletterMail($this->subscriber, $this->newsData));
    }
}
