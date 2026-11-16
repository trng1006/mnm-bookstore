<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Author extends Model implements AuditableContract
{
    use Auditable;

    protected $guarded = [];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }
}
