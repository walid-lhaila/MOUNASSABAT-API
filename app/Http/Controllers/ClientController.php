<?php

namespace App\Http\Controllers;

use App\Models\Annonce;
use App\Models\Client;
use App\Models\Favoris;
use App\Models\Reclamation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
  

    public function reclamation(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'client') {
            return response()->json(['error' => 'Only clients can create reclamations.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $reclamation = Reclamation::create([
            'message' => $request->message,
            'user_id' => $user->id,
        ]);

        return response()->json([
            "status" => "success",
            "message" => "Reclamation created successfully",
            "reclamation" => $reclamation
        ], 201);
    }

    public function favoris(Request $request)
    {
        $user = Auth::user();
    
        if ($user->role !== 'client') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        $validator = Validator::make($request->all(), [
            'annonce_id' =>'required|integer',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
    
        $favoris = Favoris::where('user_id', $user->id)
                          ->where('annonce_id', $request->annonce_id)
                          ->first();
    
        if ($favoris) {
            $favoris->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'favoris removed successfully',
                'favoris' => null  
            ], 200);
        } 
        else {
            $favoris = Favoris::create([
                'annonce_id' => $request->annonce_id,
                'user_id' => $user->id,
            ]);
    
            return response()->json([
                'status' => 'success',
                'message' => 'favoris set successfully',
                'favoris' => $favoris
            ], 201);
        }
    }

    public function checkFavoris(Request $request)
    {
        $user = Auth::user();
        $annonceId = $request->input('annonce_id');

        $favoris = Favoris::where('user_id', $user->id)
                        ->where('annonce_id', $annonceId)
                        ->exists();

        return response()->json(['favorited' => $favoris]);
    }


    
    public function getAnnonces()
    {
        $Annonces = Annonce::whereNotNull('accepted_at')->with('user')->paginate(6);
        return response()->json($Annonces);
    }

    public function getAllAcceptedAnnonces()
    {
        $Annonces = Annonce::whereNotNull('accepted_at')->with('user')->paginate(50);
        return response()->json($Annonces);
    }


  
}
