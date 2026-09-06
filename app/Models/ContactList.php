<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactList extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_marketable_only',
        'created_by_user_id',
        'cached_count',
    ];

    protected $casts = [
        'is_marketable_only' => 'boolean',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function members()
    {
        return $this->hasMany(ContactListMember::class);
    }

    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'contact_list_members');
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }
}
