<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Profile\UpdateProfileRequest;
use App\Http\Resources\Api\ProfileResource;
use App\Models\Customer;
use App\Services\ProfileService\TodoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{

    public function me(): ProfileResource
    {
        /** @var Customer $profile */
        $customer = Auth::user()->customer;
        return new ProfileResource($customer);
    }

    public function update(UpdateProfileRequest $request)
    {
        /** @var Customer $profile */
        $customer = Auth::user()->customer;
        $customer->fill($request->only(['name', 'family']));
        $customer->save();
        return new ProfileResource($customer);
    }
}
