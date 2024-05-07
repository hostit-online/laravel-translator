<?php

namespace HostitOnline\LaravelTranslator\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Translations extends Model
{
    /** @var string $table */
    protected $table = 'translations';

    /**
     * @var array|string[]
     */
    public $fillable = [
        'value',
        'translatable_id',
        'translatable_type',
        'iso_code',
        'translatable_column'
    ];

    public function translatable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'translatable_type', 'translatable_id');
    }
}
