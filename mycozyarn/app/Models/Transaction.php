<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';

    public $timestamps = false;

    protected $fillable = [
        'sepay_id',
        'gateway',
        'transaction_date',
        'account_number',
        'sub_account',
        'code',
        'amount_in',
        'amount_out',
        'accumulated',
        'content',
        'reference_code',
        'body',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'amount_in'        => 'int',
        'amount_out'       => 'int',
        'accumulated'      => 'int',
    ];

    public function getAmount(): int
    {
        return $this->amount_in - $this->amount_out;
    }
}
