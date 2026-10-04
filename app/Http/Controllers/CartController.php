<?php

namespace App\Http\Controllers;

use App\Enums\BookStatusEnum;
use App\Enums\LoanStatusEnum;
use App\Http\Resources\CartResource;
use App\Models\BookExemplar;
use App\Models\BookLoan;
use App\Models\Cart;
use App\Models\CartDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $cart = Cart::with('details.exemplar')->whereLoanerId($request->user()->id)->firstOrCreate(['loaner_id' => $request->user()->id]);
        Gate::authorize('view', $cart);
        return new CartResource($cart);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'book_exemplar_id' => ['required', 'exists:book_exemplars,id']
        ]);
        $bookExemplar = BookExemplar::findOrFail($request->book_exemplar_id);
        if ($bookExemplar->status !== BookStatusEnum::Available) {
            return response()->json([
                'message' => 'Eksemplar buku tidak tersedia.'
            ], 400);
        }
        $cart = Cart::firstOrCreate([
            'loaner_id' => $request->user()->id
        ]);
        $cart->details()->firstOrCreate([
            'book_exemplar_id' => $bookExemplar->id
        ]);
        $cart->load('details');
        return new CartResource($cart);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $cartDetail = CartDetail::findOrFail($id);
        Gate::authorize('delete', $cartDetail);
        $cartDetail->delete();
        return response()->json([
            'message' => 'Eksemplar buku berhasil dihapus dari keranjang.'
        ]);
    }

    public function checkout(Request $request)
    {
        $user = $request->user();
        $cart = Cart::with('details')->whereLoanerId($user->id)->first();

        if (!$cart || $cart->details->isEmpty()) {
            return response()->json([
                'message' => 'Keranjangmu masih kosong.'
            ], 400);
        }

        return DB::transaction(function () use ($cart, $user) {
            $loan = BookLoan::create([
                'loaner_id' => $user->id,
                'loan_time' => now(),
                'loan_status' => LoanStatusEnum::Requesting->value
            ]);
            foreach ($cart->details as $item) {
                $loan->details()->create([
                    'exemplar_book_id' => $item->book_exemplar_id
                ]);
            }
            $cart->details()->delete();
            return response()->json([
                'message' => 'Keranjang berhasil di checkout.'
            ]);
        });
    }
}
