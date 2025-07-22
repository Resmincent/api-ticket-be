<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'title',
        'description',
        'status',
        'priority',
        'completed_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class, 'ticket_id', 'id');
    }
}
