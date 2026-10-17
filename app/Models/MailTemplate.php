<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailTemplate extends Model
{
    protected $table = 'mail_templates';
    protected $primaryKey = 'mail_template_id';

    protected $fillable = [
        'subject',
        'body',
        'sender_email',
        'sender_name',
        'alias',
        'is_active',
        'bcc_copy',
        'is_bulk_mail',
    ];
}
