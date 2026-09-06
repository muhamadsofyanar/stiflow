<?php

namespace App\Models;

use App\Enums\ContactStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'status',
        'full_name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'whatsapp',
        'address',
        'city',
        'province',
        'birthday',
        'occupation',
        'notes',
        'owner_promoter_profile_id',
        'owner_user_id',
        'user_linked_id',
        'stage_id',
        'pipeline_id',
        'estimated_value',
        'score_points',
        'converted_at',
        'next_followup_at',
        'source_channel',
        'referred_by_promoter_profile_id',
        'meta',
    ];

    protected $casts = [
        'status' => ContactStatus::class,
        'birthday' => 'date',
        'converted_at' => 'datetime',
        'next_followup_at' => 'date',
        'meta' => 'json',
    ];

    public function ownerPromoterProfile()
    {
        return $this->belongsTo(PromoterProfile::class, 'owner_promoter_profile_id');
    }

    public function ownerUser()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function linkedUser()
    {
        return $this->belongsTo(User::class, 'user_linked_id');
    }

    public function stage()
    {
        return $this->belongsTo(PipelineStage::class, 'stage_id');
    }

    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function referredByPromoterProfile()
    {
        return $this->belongsTo(PromoterProfile::class, 'referred_by_promoter_profile_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'contact_tag');
    }

    public function customValues()
    {
        return $this->hasMany(ContactCustomValue::class);
    }

    public function activities()
    {
        return $this->hasMany(ContactActivity::class);
    }

    public function crmTasks()
    {
        return $this->hasMany(CrmTask::class);
    }

    public function referralVisits()
    {
        return $this->hasMany(ReferralVisit::class, 'converted_to_contact_id');
    }

    public function referralsAsReferred()
    {
        return $this->hasMany(Referral::class, 'referred_contact_id');
    }

    public function stifinResults()
    {
        return $this->hasMany(StifinResult::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(ContactSubscription::class);
    }

    public function listMemberships()
    {
        return $this->hasMany(ContactListMember::class);
    }

    public function lists()
    {
        return $this->belongsToMany(ContactList::class, 'contact_list_members');
    }

    public function campaignRecipients()
    {
        return $this->hasMany(CampaignRecipient::class);
    }

    public function messageDeliveries()
    {
        return $this->hasMany(MessageDelivery::class);
    }

    public function automationRuns()
    {
        return $this->hasMany(AutomationRun::class);
    }
}
