<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactListMember extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'contact_list_id',
        'contact_id',
        'added_at',
        'source_tag',
    ];

    protected $casts = [
        'added_at' => 'datetime',
    ];

    public function contactList()
    {
        return $this->belongsTo(ContactList::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
