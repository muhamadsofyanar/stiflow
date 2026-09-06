<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StifinResult extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'contact_id',
        'member_user_id',
        'uploaded_by_user_id',
        'promoter_owner_profile_id',
        'name',
        'result_type',
        'main_result_json',
        'elements_json',
        'summary_text',
        'pdf_file_path',
        'source_file_name',
        'report_batch_number',
        'test_taken_date',
        'valid_until_date',
        'is_sensitive_locked',
        'access_granted_by_user_id',
        'generated_at',
        'linked_course_ids_json',
    ];

    protected $casts = [
        'main_result_json' => 'json',
        'elements_json' => 'json',
        'test_taken_date' => 'date',
        'valid_until_date' => 'date',
        'is_sensitive_locked' => 'boolean',
        'generated_at' => 'datetime',
        'linked_course_ids_json' => 'json',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function memberUser()
    {
        return $this->belongsTo(User::class, 'member_user_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function promoterOwnerProfile()
    {
        return $this->belongsTo(PromoterProfile::class, 'promoter_owner_profile_id');
    }

    public function accessGrantedBy()
    {
        return $this->belongsTo(User::class, 'access_granted_by_user_id');
    }
}
