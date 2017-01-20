<?php

namespace App\Models\ECommerce;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    private static $params;

    protected static $writable_columns = [
        'customer_id',
        'ip_address', 'email', 'phone',
        'full_name', 'billing_address', 'shipping_address',
        'qty', 'discount', 'shipping', 'tax', 'total',
        'payment_type_id'
    ];

    public function __construct(array $attributes = [])
    {
        $this->fillable(self::$writable_columns);
        parent::__construct($attributes);
    }

    /**
     * Get single data
     *
     * @param $id
     * @param string $column
     * @return null
     */
    public static function single($id, $column = 'id')
    {
        if (!$id) {
            return null;
        }

        return self::get([
            'single' => true,
            $column => $id
        ]);
    }

    /**
     * Get data
     *
     * @param array $params
     * @return null
     */
    public static function get($params = [])
    {
        $select[] = 'orders.*';

        $select[] = DB::raw('payment_types.name as payment_type_name');

        $query = self::select($select)
            ->join('payment_types', 'orders.payment_type_id', '=', 'payment_types.id');

        if (isset($params['id'])) {
            $query->where('orders.id', $params['id']);
        }

        if (isset($params['customer_id'])) {
            $query->where('orders.customer_id', $params['customer_id']);
        }

        if (isset($params['exclude'])) {
            $exclude = $params['exclude'];
            foreach ($exclude['val'] as $key => $val) {
                $query->where('orders.' . $exclude['key'], '<>', $val);
            }
        }

        if (isset($params['search'])) {
            self::$params = $params;
            $query->where(function ($query) {
                $query->where('orders.email', 'LIKE', '%' . self::$params['search'] . '%')
                    ->orWhere('orders.phone', 'LIKE', '%' . self::$params['search'] . '%')
                    ->orWhere('orders.full_name', 'LIKE', '%' . self::$params['search'] . '%')
                    ->orWhere('orders.billing_address', 'LIKE', '%' . self::$params['search'] . '%')
                    ->orWhere('orders.shipping_address', 'LIKE', '%' . self::$params['search'] . '%')
                    ->orWhere('payment_types.name', 'LIKE', '%' . self::$params['search'] . '%');
            });
        }

        $query->orderBy('orders.created_at', 'DESC');

        if (isset($params['object'])) {
            return $query;
        } else {
            if (isset($params['single'])) {
                return self::_format($query->first(), $params);
            } else if (isset($params['all'])) {
                return self::_format($query->get(), $params);
            } else {
                $query = paginate($query);

                return self::_format($query, $params);
            }
        }
    }

    /**
     * Add formatting on data
     *
     * @param $query
     * @param array $params
     * @return null
     */
    private static function _format($query, $params = [])
    {
        if (isset($params['single'])) {
            if (!$query) {
                return null;
            }

            $query->formatted_discount = money($query->discount);
            $query->formatted_shipping = money($query->shipping);
            $query->formatted_tax = money($query->tax);
            $query->formatted_total = money($query->total);
        } else {
            foreach ($query as $row) {
                $row->formatted_discount = money($row->discount);
                $row->formatted_shipping = money($row->shipping);
                $row->formatted_tax = money($row->tax);
                $row->formatted_total = money($row->total);
            }
        }

        return $query;
    }

    /**
     * Store new data
     *
     * @param array $inputs
     * @return bool
     */
    public static function store($inputs = [])
    {
        $store = [];

        foreach ($inputs as $key => $value) {
            if (in_array($key, self::$writable_columns)) {
                $store[$key] = $value;
            }
        }

        $store['created_at'] = sql_date();
        return (int)self::insertGetId($store);
    }

    /**
     * Delete data
     *
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public static function remove($id)
    {
        return (bool)self::destroy($id);
    }

    /**
     * Update data
     *
     * @param $id
     * @param array $inputs
     * @param null $column_name
     * @return bool
     */
    public static function edit($id, $inputs = [], $column_name = null)
    {
        $update = [];
        $query = null;

        if (!$column_name) {
            $column_name = 'id';
        }

        if ($id && !is_array($column_name)) {
            $query = self::where($column_name, $id);
        } else {
            $i = 0;
            foreach ($column_name as $key => $value) {
                if (!in_array($key, self::$writable_columns)) {
                    return false;
                }

                if (!$i) {
                    $query = self::where($key, $value);
                } else {
                    if ($query) {
                        $query->where($key, $value);
                    }
                }
                $i++;
            }
        }

        foreach ($inputs as $key => $value) {
            if (in_array($key, self::$writable_columns)) {
                $update[$key] = $value;
            }
        }

        return (bool)$query->update($update);
    }
}
