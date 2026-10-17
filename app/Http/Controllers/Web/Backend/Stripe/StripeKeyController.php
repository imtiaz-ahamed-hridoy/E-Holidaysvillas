<?php

namespace App\Http\Controllers\Web\Backend\Stripe;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class StripeKeyController extends Controller
{
   public function edit()
    {
        return view('backend.layouts.settings.stripe', [
            'stripe_key'    => config('services.stripe.key'),
            'stripe_secret' => config('services.stripe.secret'),
            'webhook_key'   => config('services.stripe.webhook'),
        ]);
    }


    public function update(Request $request)
    {
        $request->validate([
            'stripe_key' => 'required|string',
            'stripe_secret' => 'required|string',
            'webhook_key'   => 'required|string'
        ]);

        $this->setEnv([
            'STRIPE_KEY' => $request->stripe_key,
            'STRIPE_SECRET' => $request->stripe_secret,
            'STRIPE_WEBHOOK_SECRET' => $request->webhook_key,
        ]);

        // Optional: clear config cache
        Artisan::call('config:clear');

        return redirect()->route('stripe.edit')->with('t-success', 'Stripe keys updated successfully!');
    }

    private function setEnv(array $data)
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            if (file_exists(base_path('.env.example'))) {
                copy(base_path('.env.example'), $envPath);
            } else {
                file_put_contents($envPath, '');
            }
        }
        $envContent = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            $pattern = "/^{$key}=.*/m";

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, "{$key}={$value}", $envContent);
            } else {
                // Add new key if not exists
                $envContent .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envPath, $envContent);
    }
}
