<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MailerSend\Helpers\Builder\Personalization;
use MailerSend\Helpers\Builder\Variable;
use MailerSend\LaravelDriver\MailerSendTrait;

class TestEmail extends Mailable
{
    use Queueable, SerializesModels, MailerSendTrait;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        public Carbon $date,
        public string $note,
    ) {}

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Parts Tracker Test Email ' . date_format($this->date, 'Y-m-d'),
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        $to = Arr::get($this->to, '0.address');

        // Additional options for MailerSend API features
        $this->mailersend(
            template_id: null,
            variables: [
                new Variable($to, ['name' => 'Your Name'])
            ],
            tags: ['tag'],
            personalization: [
                new Personalization($to, [
                    'var' => 'variable',
                    'number' => 123,
                    'object' => [
                        'key' => 'object-value'
                    ],
                    'objectCollection' => [
                        [
                            'name' => 'John'
                        ],
                        [
                            'name' => 'Patrick'
                        ]
                    ],
                ])
            ],
            precedenceBulkHeader: true,
            sendAt: new Carbon('2022-01-28 11:53:20'),
        );

        return new Content(
            text: 'emails.testemail',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
