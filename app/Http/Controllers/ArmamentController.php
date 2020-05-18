<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Armament;

class ArmamentController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(Armament::all());
    }

    public function show(Request $request, $id)
    {
        $armament = Armament::find($id);
        
        if(empty($armament))
            return response()->json(['success' => false, "message" => "Armament not found."], 406);
        
       return response()->json($armament);
    }

    public function store(Request $request)
    {
        try {
            $values = $this->validateArmament($request);
        
            $armament = Armament::create($values);
            $armament->save();

            $success = true;
        } catch (\Illuminate\Database\QueryException $ex) {
            $success = false;
        }

        return response()->json(["success" => $success]);
    }

    public function update(Request $request, $id)
    {
        $armament = Armament::find($id);
        if(empty($armament))
            return response()->json(['success' => false, "message" => "This is not the armament you are looking for."], 406);

        try {
            $values = $this->validateArmament($request);
            $armament->update($values);

            $success = true;
        } catch (\Illuminate\Database\QueryException $ex) {
            $success = false;
        }

        return response()->json(["success" => $success]);
    }

    public function destroy(Request $request, $id)
    {
        $armament = Armament::find($id);
        if(empty($armament))
            return response()->json(['success' => false, "message" => "Destroy me, you can not."], 406);

        try {
            $armament->delete();
            $success = true;
        } catch (\Illuminate\Database\QueryException $ex) {
            $success = false;
        }

        return response()->json(["success" => $success]);
    }

    private function validateArmament(Request $request)
    {
        $values = $this->validate($request, [
            'title' => 'required'
        ]);
        
        return $values;
    }

    protected function buildFailedValidationResponse(Request $request, array $errors) 
    {
        return ["success" => false , "errors" => $errors];
    }
}
