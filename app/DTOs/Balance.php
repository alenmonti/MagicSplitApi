<?php

namespace App\DTOs;

class Balance
{
    public $user_id;
    public $name;
    public $balance;

    public function __construct($user_id, $name,$balance)
    {
        $this->user_id = $user_id;
        $this->name = $name;
        $this->balance = $balance;
    }
}