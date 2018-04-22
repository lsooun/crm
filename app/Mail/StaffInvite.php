<?php

namespace App\Mail;

use App\Models\InviteUser;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class StaffInvite extends Mailable
{
    use Queueable, SerializesModels;
	/**
	 * @var InviteUser
	 */
	private $inviteUser;
	private $emailSettings;
	private $siteNameSettings;

	/**
	 * Create a new message instance.
	 *
	 * @param InviteUser $inviteUser
	 */
    public function __construct(InviteUser $inviteUser)
    {
	    $this->inviteUser = $inviteUser;
	    $this->emailSettings = Settings::get('site_email');
	    $this->siteNameSettings = Settings::get('site_name');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
	    $invite = $this->inviteUser;
	    return $this->from($this->emailSettings, $this->siteNameSettings)
	                ->view('emails.invite', compact('invite'));
    }
}
