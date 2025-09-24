<?php

namespace App\Mail;

use App\Models\DogTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TransferInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DogTransfer $transfer, public string $rawToken) {}

    public function build()
    {
        $url = url("/transfer/accept/{$this->transfer->id}?t={$this->rawToken}");
        $expires = $this->transfer->expires_at->toDayDateTimeString();

        return $this->subject("You've been invited to take ownership of {$this->transfer->dog->name}")
            ->markdown('mail.transfers.invite', [
                'transfer' => $this->transfer,
                'url' => $url,
                'expires' => $expires,
                'code' => strtoupper('KKT-'.substr(hash('crc32b',$this->rawToken),0,3).'-'.substr(hash('md5',$this->rawToken),0,3).'-'.substr(hash('sha1',$this->rawToken),0,3)),
            ]);
    }
}
