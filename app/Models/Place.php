<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\PlaceType;

class Place extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type_id','name','address','phone','lat','lng','status','icon','thumbnail','time','description'];

    // relation to PlaceType model
    public function type()
    {
        return $this->belongsTo(\App\Models\PlaceType::class, 'type_id');
    }

    public function getTypeNameAttribute()
    {
        return $this->type?->name ?? null;
    }

    /**
     * Get the label for the place type.
     *
     * @return string|null
     */
    public function getTypeLabelAttribute()
    {
        return $this->type?->label() ?? null;
    }

    /**
     * Get the default icon for the place.
     *
     * @return string|null
     */
    public function getDefaultIconAttribute()
    {
        return $this->type?->icon() ?? null;
    }
}
