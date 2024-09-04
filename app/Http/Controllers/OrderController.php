<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        // Validar la solicitud
        $request->validate([
            'total_amount' => 'required|numeric|min:0.01',
        ]);

        // Obtener el usuario autenticado
        $user = Auth::user();

        // Crear el pedido en la base de datos
        $order = Order::create([
            'user_id' => $user->id,
            'total_amount' => $request->total_amount,
            'payment_status' => 'pending',
        ]);

/*
        $stripe = new \Stripe\StripeClient('sk_test_51KgkqbB4YW9wI5S4M8dfcIXlpMuNMO2nKqVe5aMcWuHUkY6KP9guwdqzqUuo708qVxliP8mRgPIa0Y9hKO5nkbsv00fwy8fdP4');

        try {
            $stripe->paymentIntents->create([
                'amount' => $request->total_amount,
                'currency' => 'usd',
                'payment_method' => 'pm_card_visa',
              ]);
            }
*/

        // Simular llamada a la API de Stripe
        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $request->total_amount,
                'currency' => 'usd',
                //'payment_method_types' => ['card'],
                'payment_method' => 'pm_card_visa',
            ]);

            // Actualizar el estado del pedido
            $order->payment_status = 'paid';
            $order->save();

            // Respuesta con detalles del pedido y resultado del pago
            return response()->json([
                'order' => $order,
                'payment_intent' => $paymentIntent,
            ], 201);
        } catch (\Exception $e) {
            // En caso de error, actualizar el estado del pedido a fallido
            $order->payment_status = 'failed';
            $order->save();

            return response()->json([
                'message' => 'Payment failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
