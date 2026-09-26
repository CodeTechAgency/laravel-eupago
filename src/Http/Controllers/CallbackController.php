<?php

namespace CodeTech\EuPago\Http\Controllers;

use CodeTech\EuPago\Enums\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rules\Enum;

class CallbackController extends Controller
{
    /**
     * This endpoint is called when a payment of any method is confirmed.
     *
     * Eupago's backoffice takes a single notification URL per channel and
     * sends every payment method's notification to it, so the payment method
     * (`mp`) picks the controller that handles it. A route can default the
     * method for notifications whose `mp` the package does not know.
     *
     * @return JsonResponse
     */
    public function callback(Request $request)
    {
        $method = $this->paymentMethod($request);

        if ($method === null) {
            // A method the package does not handle: validating it rejects the
            // notification, after checking the caller as any callback does.
            $validatedData = $this->validateCallback($request, ['mp' => ['required', new Enum(PaymentMethod::class)]]);
            $method = PaymentMethod::from($validatedData['mp']);
        }

        return App::make($this->controllerFor($method))->callback($request);
    }

    /**
     * Get the payment method the notification names, or else the route's default.
     */
    private function paymentMethod(Request $request): ?PaymentMethod
    {
        foreach ([$request->input('mp'), $request->route('default_payment_method')] as $code) {
            if (is_string($code) && $method = PaymentMethod::tryFrom($code)) {
                return $method;
            }
        }

        return null;
    }

    /**
     * Get the controller that handles the payment method.
     *
     * @return class-string<Controller>
     */
    private function controllerFor(PaymentMethod $method): string
    {
        return match ($method) {
            PaymentMethod::Multibanco => MBController::class,
            PaymentMethod::MbWay => MBWayController::class,
            PaymentMethod::PayShop => PayShopController::class,
            PaymentMethod::PaysafeCard => PaysafeCardController::class,
            PaymentMethod::CreditCard => CreditCardController::class,
        };
    }
}
