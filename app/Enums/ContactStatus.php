<?php

namespace App\Enums;

enum ContactStatus: string
{
    case Prospect = 'prospect';
    case Qualified = 'qualified';
    case Client = 'client';
    case Member = 'member';
    case CandidatePromoter = 'candidate_promoter';
    case Lost = 'lost';
    case Won = 'won';
}
