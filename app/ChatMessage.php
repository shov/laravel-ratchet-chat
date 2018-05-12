<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $res_id
 * @property string $msg
 */
class ChatMessage extends Model
{
    protected $fillable = ['res_id', 'msg'];
}
