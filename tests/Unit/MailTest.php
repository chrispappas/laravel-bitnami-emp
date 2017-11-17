<?php

namespace Tests\Unit;

use App\Mail\HelloWorld;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailTest extends TestCase
{
    public function testSendEmail()
    {
        $emailData = [
            'from'      => 'me@foo.me',
            'recipient' => 'another@person.who'
        ];

        Mail::to($emailData['recipient'])
            ->send(new HelloWorld($emailData));

        $this->assertCount(0, Mail::failures());
    }
}
