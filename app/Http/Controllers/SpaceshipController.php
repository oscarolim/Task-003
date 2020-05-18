<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Validation\ValidArmament;
use App\Armament;
use App\Spaceship;

class SpaceshipController extends Controller
{
    //Valid statuses for the spaceships
    const STATUS = ['operational', 'damaged', 'battle', 'patrol'];

    public function index(Request $request)
    {
        $filter = $this->filterBy($request);
        return response()->json(Spaceship::select(['id', 'name', 'status'])->where($filter)->get());
    }

    public function show(Request $request, $id)
    {
        $spaceship = Spaceship::with('armament')->find($id);
        
        if(empty($spaceship))
            return response()->json(['success' => false, "message" => "Spaceship not found. Maybe the rebels have destroyed it?"], 406);
        
        foreach($spaceship->armament as $armament)
        {
            $armament->qty = $armament->pivot->quantity;
            unset($armament->id);
            unset($armament->pivot);
        }
        
        return response()->json($spaceship);
    }

    public function store(Request $request)
    {
        try {
            $values = $this->validateSpaceship($request);
        
            $spaceship = Spaceship::create($values);
            $spaceship->save();
            //$this->storeUpdateArmamentConfiguration($values['armament'], $spaceship->id);
            $spaceship->image = $this->storeImage($request, $spaceship->id);
            $spaceship->save();

            $success = true;
        } catch (\Illuminate\Database\QueryException $ex) {
            $success = false;
        }

        return response()->json(["success" => $success]);
    }

    public function update(Request $request, $id)
    {
        $spaceship = Spaceship::find($id);
        if(empty($spaceship))
            return response()->json(['success' => false, "message" => "This is not the spaceship you are looking for."], 406);

        try {
            $values = $this->validateSpaceship($request);
            $spaceship->update($values);
            //$this->storeUpdateArmamentConfiguration($values['armament'], $spaceship->id);
            $spaceship->image = $this->storeImage($request, $spaceship->id);
            $spaceship->save();

            $success = true;
        } catch (\Illuminate\Database\QueryException $ex) {
            $success = false;
        }

        return response()->json(["success" => $success]);
    }

    public function destroy(Request $request, $id)
    {
        $spaceship = Spaceship::find($id);
        if(empty($spaceship))
            return response()->json(['success' => false, "message" => "Destroy me, you can not."], 406);

        try {
            $spaceship->delete();
            $success = true;
        } catch (\Illuminate\Database\QueryException $ex) {
            $success = false;
        }

        return response()->json(["success" => $success]);
    }

    private function validateSpaceship(Request $request)
    {
        $values = $this->validate($request, [
            'name' => 'required',
            'class' => 'required',
            'crew' => 'required|integer|gte:0',
            'value' => 'required|numeric|gte:0',
            'image' => 'image',
            'status' => [
                'required',
                Rule::in(self::STATUS),
            ],
            //'armament' => [new ValidArmament] //Custom rule to confirm the received information is in the right format and is valid - uses App/ValidArmament.php
            ],
            [
                'status.in' => 'The selected status is invalid. Use one of ['.implode('|', self::STATUS).']'
            ]);
        
        return $values;
    }

    private function filterBy(Request $request)
    {
        //Returns a valid list of possible filtering options

        $filter = [];
        $filter['name'] = $request->input('name') ?? '';
        $filter['class'] = $request->input('class') ?? '';
        $filter['status'] = $request->input('status') ?? '';
        return array_filter($filter);
    }

    public function installArmament(Request $request, $id)
    {
        $this->validate($request, [
            'quantity' => 'required|numeric',
            'armament_id' => 'required|exists:armaments,id'
        ]);

        $armament = Armament::find($request->input('armament_id'));
        $spaceship = Spaceship::find($id);
        if(empty($spaceship))
            return response()->json(["success" => false , "message" => "This is not the starship you are looking for."], 406);

        $uniqueFields = [
            'armament_id' => $armament->id,
            'spaceship_id' => $spaceship->id
        ];
        try {
            app('db')->table('armament_spaceship')->updateOrInsert($uniqueFields, ['quantity' => $request->input('quantity')]);
            $success = true;
        } catch (\Illuminate\Database\QueryException $ex) {
            $success = false;
        }
        return response()->json(["success" => $success]);
    }

    public function removeArmament(Request $request, $id)
    {
        $armament = Armament::find($request->input('armament_id'));
        if(empty($armament))
            return response()->json(["success" => false , "message" => "This is not the armament you are looking for."], 406);

        $spaceship = Spaceship::find($id);
        if(empty($spaceship))
            return response()->json(["success" => false , "message" => "This is not the starship you are looking for."], 406);

        if($spaceship->armament->contains($armament))
        {
            try {
                app('db')->table('armament_spaceship')
                    ->where('spaceship_id', $spaceship->id)
                    ->where('armament_id', $armament->id)
                    ->delete();

                $success = true;
            } catch (\Illuminate\Database\QueryException $ex) {
                $success = false;
            }
            return response()->json(["success" => $success]);
        }
        else
            return response()->json(["success" => false , "message" => "This starship doesn not have this armament installed."], 406);
    }

    //Not in use
    /*private function storeUpdateArmamentConfiguration($armament_string, $spaceship_id)
    {
        if($armament_string)
        {
            $armament_configuration = array_map(function($el){return explode('|', $el);}, explode(',', preg_replace('/\s+/', '', $armament_string)));
            foreach($armament_configuration as $armament)
            {
                $uniqueFields = [
                    'armament_id' => (int)$armament[0],
                    'spaceship_id' => $spaceship_id
                ];
                app('db')->table('armament_spaceship')->updateOrInsert($uniqueFields, ['quantity' => (int)$armament[1]]);
            }
        }
    }*/

    private function storeImage($request, $spaceship_id)
    {
        if($request->hasFile('image') && $request->file('image')->isValid()) 
        {
            $destinationPath = '/uploads/';
            $request->file('image')->move($destinationPath);
            return $destinationPath.$request->file('image')->getClientOriginalName();
        }
    }

    protected function buildFailedValidationResponse(Request $request, array $errors) 
    {
        return ["success" => false , "errors" => $errors];
    }
}
