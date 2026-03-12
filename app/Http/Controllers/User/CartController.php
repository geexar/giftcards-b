<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CartItemRequest;
use App\Http\Resources\User\CartResource;
use App\Services\User\CartService;

class CartController extends Controller
{
    public function __construct(private CartService $cartService) {}

    public function get()
    {
        $cart = $this->cartService->getCart();

        return success(CartResource::make($cart));
    }

    public function increase(CartItemRequest $request)
    {
        $this->cartService->increaseItemQuantity($request->type, $request->id);

        return success(true);
    }

    public function decrease(CartItemRequest $request)
    {
        $this->cartService->decreaseItemQuantity($request->type, $request->id);

        return success(true);
    }

    public function delete(CartItemRequest $request)
    {
        $this->cartService->removeItemFromCart($request->type, $request->id);

        return success(true);
    }

    public function preCheckoutValidation()
    {
        $messages = $this->cartService->preCheckoutValidation();

        return success($messages);
    }
}
