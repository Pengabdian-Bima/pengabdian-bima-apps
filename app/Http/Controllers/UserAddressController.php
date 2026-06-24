<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserAddressController extends Controller
{
    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('UserAddressController@store request data: ', $request->all());

        try {
            $request->validate([
                'label' => 'required|string|max:100',
                'recipient_name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'address' => 'required|string',
                'province' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'city_id' => 'nullable',
                'district' => 'nullable|string|max:255',
                'village' => 'nullable|string|max:255',
                'postal_code' => 'nullable|string|max:10',
                'is_default' => 'boolean',
            ]);
            \Illuminate\Support\Facades\Log::info('UserAddressController@store validation passed');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('UserAddressController@store validation failed: ', $e->errors());
            throw $e;
        }

        $userId = auth()->id();
        $isFirst = UserAddress::where('user_id', $userId)->count() === 0;
        $isDefault = $request->boolean('is_default') || $isFirst;

        DB::transaction(function () use ($request, $userId, $isDefault) {
            if ($isDefault) {
                UserAddress::where('user_id', $userId)->update(['is_default' => false]);
            }

            $created = UserAddress::create([
                'user_id' => $userId,
                'label' => $request->label,
                'recipient_name' => $request->recipient_name,
                'phone' => $request->phone,
                'address' => $request->address,
                'province' => $request->province,
                'city' => $request->city,
                'city_id' => $request->city_id,
                'district' => $request->district,
                'village' => $request->village,
                'postal_code' => $request->postal_code,
                'is_default' => $isDefault,
            ]);
            \Illuminate\Support\Facades\Log::info('UserAddressController@store address created ID: ' . $created->id);
        });

        return back()->with('success', 'Alamat berhasil ditambahkan.');
    }

    public function update(Request $request, UserAddress $address)
    {
        \Illuminate\Support\Facades\Log::info('UserAddressController@update request data: ', $request->all());

        if ($address->user_id !== auth()->id()) {
            \Illuminate\Support\Facades\Log::warning('UserAddressController@update unauthorized access', [
                'address_user_id' => $address->user_id,
                'auth_id' => auth()->id()
            ]);
            abort(403);
        }

        try {
            $request->validate([
                'label' => 'required|string|max:100',
                'recipient_name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'address' => 'required|string',
                'province' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'city_id' => 'nullable',
                'district' => 'nullable|string|max:255',
                'village' => 'nullable|string|max:255',
                'postal_code' => 'nullable|string|max:10',
                'is_default' => 'boolean',
            ]);
            \Illuminate\Support\Facades\Log::info('UserAddressController@update validation passed');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('UserAddressController@update validation failed: ', $e->errors());
            throw $e;
        }

        $isDefault = $request->boolean('is_default');

        DB::transaction(function () use ($request, $address, $isDefault) {
            if ($isDefault) {
                UserAddress::where('user_id', auth()->id())
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }

            $address->update([
                'label' => $request->label,
                'recipient_name' => $request->recipient_name,
                'phone' => $request->phone,
                'address' => $request->address,
                'province' => $request->province,
                'city' => $request->city,
                'city_id' => $request->city_id,
                'district' => $request->district,
                'village' => $request->village,
                'postal_code' => $request->postal_code,
                'is_default' => $isDefault,
            ]);
            \Illuminate\Support\Facades\Log::info('UserAddressController@update address updated ID: ' . $address->id);
        });

        return back()->with('success', 'Alamat berhasil diperbarui.');
    }

    public function destroy(UserAddress $address)
    {
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        $wasDefault = $address->is_default;

        DB::transaction(function () use ($address, $wasDefault) {
            $address->delete();

            if ($wasDefault) {
                $nextAddress = UserAddress::where('user_id', auth()->id())->first();
                if ($nextAddress) {
                    $nextAddress->update(['is_default' => true]);
                }
            }
        });

        return back()->with('success', 'Alamat berhasil dihapus.');
    }

    public function setDefault(UserAddress $address)
    {
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        DB::transaction(function () use ($address) {
            UserAddress::where('user_id', auth()->id())->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return back()->with('success', 'Alamat default berhasil diubah.');
    }
}
