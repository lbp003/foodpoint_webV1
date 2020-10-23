<?php

/**
 * MenuItemModifierItem Model
 *
 * @package    GoferEats
 * @subpackage Model
 * @category   MenuItemModifierItem
 * @author     Trioangle Product Team
 * @version    1.2
 * @link       http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use JWTAuth;

class MenuItemModifierItem extends Model
{
    // Change model translatable
    use Translatable;

    /**
     * Indicates Which attributes are translated.
     *
     * @var Array
     */
    public $translatedAttributes = ['name'];
    
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $table = 'menu_item_modifier_item';

    protected $appends = ['count','is_select','is_disabled'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function scopeVisible($query, $visible = 1)
    {
        return $query->where('is_visible', $visible);
    }

    public function scopeRestaurant($query, $restaurant_id)
    {
        return $query->with([
            'menu_item_modifier' => function ($query) use ($restaurant_id) {
                $query->restaurant($restaurant_id);
            }
        ])
        ->whereHas('menu_item_modifier',function ($query) use ($restaurant_id) {
            $query->restaurant($restaurant_id);
        });
    }

    public function menu_item_modifier()
    {
        return $this->belongsTo('App\Models\MenuItemModifier', 'menu_item_modifier_id', 'id');
    }

    public function getIsDisabledAttribute()
    {
        if(request()->segment(1) !='api')
        {
            return false;
        }
    }

    public function getCountAttribute()
    {
        if(request()->segment(1) != 'api') {
            return 0;
        }

        if(!request()->order_id) {
            return $this->is_select;
        }

        $order_item = OrderItem::with('order_item_modifier.order_item_modifier_item')->where('order_id',request()->order_id)->get();
        $count = 0;
        foreach ($order_item as $key => $value) {
            $menu_item = $value->order_item_modifier;
            foreach ($menu_item as $key => $value) {
                $menu_item_modifier = $value->order_item_modifier_item;
                foreach ($menu_item_modifier as $key => $value) {
                    if($this->id == $value->menu_item_modifier_item_id) {
                        $count = $value->count;
                    }
                }
            }
        }
        return $count;
    }
    
    public function getIsselectAttribute()
    {
        if(request()->segment(1) != 'api') {
            return 0;
        }
        if(!request()->order_id) {
            if($this->menu_item_modifier->max_count == 0 && $this->menu_item_modifier->min_count == 0 && $this->menu_item_modifier->is_required) {
                return 1;
            }
        }

        $order_item = OrderItem::with('order_item_modifier.order_item_modifier_item')->where('order_id',request()->order_id)->get();
        $selected =0;
        foreach ($order_item as $key => $value) {
            $menu_item = $value->order_item_modifier;
            foreach ($menu_item as $key => $value) {
                $menu_item_modifier = $value->order_item_modifier_item;
                foreach ($menu_item_modifier as $value) {
                    
                    if($value->min_count == 0 && $value->max_count == 0 && $value->is_required == 1) {
                        $selected = 1;
                    }
                    if($this->id == $value->menu_item_modifier_item_id) {
                        $selected = 1;
                    }

                }
            }
        }
        return $selected;        
    }
}