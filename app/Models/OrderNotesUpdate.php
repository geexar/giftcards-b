<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderNotesUpdate extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'order_id',
        'content',
        'created_at',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
