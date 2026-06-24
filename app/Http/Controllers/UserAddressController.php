<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserAddressController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:100',
            'recipient_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'province' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'village' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'is_default' => 'boolean',
        ]);

        $userId = auth()->id();
        $isFirst = UserAddress::where('user_id', $userId)->count() === 0;
        $isDefault = $request->boolean('is_default') || $isFirst;

        DB::transaction(function () use ($request, $userId, $isDefault) {
            if ($isDefault) {
                UserAddress::where('user_id', $userId)->update(['is_default' => false]);
            }

            UserAddress::create([
                'user_id' => $userId,
                'label' => $request->label,
                'recipient_name' => $request->recipient_name,
                'phone' => $request->phone,
                'address' => $request->address,
                'province' => $request->province,
                'city' => $request->city,
                'district' => $request->district,
                'village' => $request->village,
                'postal_code' => $request->postal_code,
                'is_default' => $isDefault,
            ]);
        });

        return back()->with('success', 'Alamat berhasil ditambahkan.');
    }

    public function update(Request $request, UserAddress $address)
    {
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'label' => 'required|string|max:100',
            'recipient_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'province' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'village' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'is_default' => 'boolean',
        ]);

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
                'district' => $request->district,
                'village' => $request->village,
                'postal_code' => $request->postal_code,
                'is_default' => $isDefault,
            ]);
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
