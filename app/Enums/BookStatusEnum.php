<?php

namespace App\Enums;

enum BookStatusEnum: string
{
    case Available = "available";
    case OnLoan = "on_loan";
    case Requesting = "requesting";
    case Disappear = "disappear";
}
