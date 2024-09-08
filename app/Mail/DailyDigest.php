<?php

namespace App\Mail;

use App\Models\Part;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use MailerSend\LaravelDriver\MailerSendTrait;

class DailyDigest extends Mailable
{
    use MailerSendTrait, Queueable, SerializesModels;

    protected $parts;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        protected User $user
    ) {
        $this->parts = Part::unofficial()
            ->whereHas('notification_users', fn (Builder $q) => $q->where('id', $this->user->id))
            ->whereHas('events', fn (Builder $q) => $q->unofficial()->whereBetween('created_at', [Carbon::yesterday(), Carbon::today()]))
            ->get();
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Parts Tracker Daily Summary for '.Carbon::yesterday()->format('Y-m-d'),
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        $this->mailersend(template_id: null);

        return new Content(
            markdown: 'emails.dailydigest-markdown',
            with: [
                'parts' => $this->parts,
                'date' => Carbon::yesterday(),
                'next' => Carbon::today(),
            ]
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
