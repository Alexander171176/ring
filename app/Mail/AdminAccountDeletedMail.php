<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class AdminAccountDeletedMail extends Mailable
{
    public function build()
    {
        return $this
            ->subject('⚠️ Учетная запись администратора удалена')
            ->view('emails.admin_deleted')
            ->with([
                'time' => now()->toDateTimeString(),
                'ip' => request()->ip(),
                'url' => request()->fullUrl(),
            ]);
    }
}
