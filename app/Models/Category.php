<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Category extends Model implements AuditableContract
{
    use Auditable;

    protected $guarded = [];

    public function books()
    {
        return $this->hasMany(Book::class);
    }
}
