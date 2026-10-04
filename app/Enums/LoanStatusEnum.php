<?php

namespace App\Enums;

enum LoanStatusEnum: string
{
    case OnLoan = "on_loan";
    case Requesting = "requesting";
    case Returned = "returned";
    case Late = "late";
    case Disappear = "disappear";
}
