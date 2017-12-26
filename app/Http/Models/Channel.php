<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    public function isDelete()
    {
        return $this->is_delete === 'T';
    }

}
