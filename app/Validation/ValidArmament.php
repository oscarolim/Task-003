<?php

namespace App\Validation;

use Illuminate\Contracts\Validation\Rule;
use App\Armament;

class ValidArmament implements Rule 
{
    public function passes($attribute, $value) 
    {
        /*
        Get a list of all ids and all quantities. If an ID doesn't exist it will return 0 (which will subsequently fail on the DB check)
        and if a quantity doesn't exist, it will return false, which will also fail the return condition and thus returning false
        */
        $armament_ids = array_map(function($el){return explode('|', $el)[0] ?? 0;}, explode(',', preg_replace('/\s+/', '', $value)));
        $armament_quantities = array_map(function($el){return explode('|', $el)[1] ?? FALSE;}, explode(',', preg_replace('/\s+/', '', $value)));
        $validArmaments = Armament::select('id')->find($armament_ids);
        return $validArmaments->count() === count($armament_ids) && !in_array(FALSE, $armament_quantities);
    }

    public function message()
    {
        return ':attribute needs to exist on the Armament list! Use /api/v1/armaments to get all valid IDs, and add a quantity too! And no duplicated IDs. Example: [id|quantity,id|quantity]';
    }
}

?>