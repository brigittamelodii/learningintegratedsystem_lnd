<?php

  

namespace App\Http\Controllers;

  

use Illuminate\Http\Request;

use App\Models\Program;
use App\Models\classes;

  

class DropdownController extends Controller

{

    /**

     * Write code on Method

     *

     * @return response()

     */

    public function index()

    {

        $data['programs'] = Program::get(["program_name", "id"]);

        return view('dropdown', $data);

    }

    /**

     * Write code on Method

     *

     * @return response()

     */

    public function fetchClass(Request $request)

    {

        $data['classes'] = classes::where("program_id", $request->program_id)

                                ->get(["class_name", "id"]);

        return response()->json($data);

    }

    public function storeSelection(Request $request)
{
    $request->validate([
        'program_id' => 'required|exists:programs,id',
        'class_id' => 'required|exists:classes,id',
    ]);

    // Simpan sementara ke session
    session([
        'selected_program_id' => $request->program_id,
        'selected_class_id' => $request->class_id,
    ]);

    // Arahkan ke step 2
    return redirect()->route('participants-payment.create');
}


}