<?php

namespace App\Enums;

enum RoleEnum: string
{
    case ADMIN = "admin";
    case STAFF = "staff";
    case LOANER = "loaner";

    public function label():string {
        return match($this) {
            self::ADMIN => "admin",
            self::STAFF => "staff",
            self::LOANER => "loaner"
        };
    }
}
