<?php

namespace Acelle\Cashier\Controllers;

use Acelle\Http\Controllers\Controller;
use Acelle\Cashier\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as LaravelLog;
use Acelle\Cashier\Cashier;

class DirectController extends Controller
{
    /**
     * Get current payment service.
     *
     * @return \Illuminate\Http\Response
     **/
    public function getPaymentService()
    {
        return Cashier::getPaymentGateway('direct');
    }
    
    /**
     * Subscription checkout page.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     **/
    public function checkout(Request $request, $subscription_id)
    {
        $service = $this->getPaymentService();
        $subscription = Subscription::findByUid($subscription_id);
        
        // Save return url
        $request->session()->put('checkout_return_url', $request->return_url);
        
        // if subscription is active
        if ($subscription->isActive()) {
            return redirect()->away($request->session()->get('checkout_return_url'));
        }
        
        $transaction = $service->getTransaction($subscription);
        
        return view('cashier::direct.checkout', [
            'gatewayService' => $service,
            'subscription' => $subscription,
            'transaction' => $transaction,
        ]);
    }
    
    /**
     * Claim payment.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     **/
    public function claim(Request $request, $subscription_id)
    {
        // subscription and service
        $subscription = Subscription::findByUid($subscription_id);
        $gatewayService = $this->getPaymentService();
        
        $gatewayService->claim($subscription);
        
        return redirect()->action('\Acelle\Cashier\Controllers\DirectController@checkout', [
            'subscription_id' => $subscription->uid,
        ]);
    }
    
    /**
     * Unclaim payment.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     **/
    public function unclaim(Request $request, $subscription_id)
    {
        // subscription and service
        $subscription = Subscription::findByUid($subscription_id);
        $gatewayService = $this->getPaymentService();
        
        $gatewayService->unclaim($subscription);
        
        return redirect()->action('\Acelle\Cashier\Controllers\DirectController@checkout', [
            'subscription_id' => $subscription->uid,
        ]);
    }
}