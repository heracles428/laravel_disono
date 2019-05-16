<?php
/**
 * @author          Archie Disono (webmonsph@gmail.com)
 * @link            https://github.com/disono/Laravel-Template
 * @copyright       Webmons Development Studio. (https://webmons.com), 2016-2019
 * @license         Apache, 2.0 https://github.com/disono/Laravel-Template/blob/master/LICENSE
 */

namespace App\Models;

use App\Models\Vendor\BaseModel;

class City extends BaseModel
{
    protected $tableName = 'cities';
    protected $writableColumns = [
        'country_id', 'name', 'lat', 'lng'
    ];

    public function __construct(array $attributes = [])
    {
        $this->fillable($this->writableColumns);
        parent::__construct($attributes);
    }

    public function user()
    {
        return $this->hasMany('App\Models\User');
    }

    public function country()
    {
        return $this->belongsTo('App\Models\Country');
    }

    public function rawFilters($query)
    {
        $query->join('countries', 'cities.country_id', '=', 'countries.id');
        return $query;
    }

    protected function rawQuerySelectList()
    {
        return [
            'country_name' => 'countries.name',
        ];
    }
}
