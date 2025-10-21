<?php
namespace App\Http\Controllers;

use App\Models\User;
use Elliptic\EC;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use kornrunner\Keccak;

class MetaMaskController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'wallet_address' => 'required|string',
        ]);

        $user = $this->userFetch($request->wallet_address);

        Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user'    => $user,
        ]);
    }

    private function userFetch(string $address): User
    {
        $user = User::where('wallet_address', $address)->first();

        if (! $user) {
            $user = User::create([
                'wallet_address' => $address,
                'name'           => 'User_' . substr($address, 2, 6),
                'email'          => $address . '@metamask.user',
                'password'       => Hash::make(Str::random(24)),
            ]);
        }

        return $user;
    }

    public function verify(Request $request)
    {
        $request->validate([
            'wallet_address' => 'required|string|regex:/^0x[a-fA-F0-9]{40}$/',
            'signature'      => 'required|string',
            'message'        => 'required|string',
        ]);

        try {
            $walletAddress = $request->wallet_address;
            $signature     = $request->signature;
            $message       = $request->message;

            // Verify the signature
            $isVerified = $this->verifySignature($message, $signature, $walletAddress);

            if ($isVerified) {
                // Find user and login
                $user = $this->userFetch($walletAddress);

                Auth::login($user);

                return response()->json([
                    'success' => true,
                    'message' => 'Signature verified and login successful',
                    'user'    => [
                        'id'             => $user->id,
                        'name'           => $user->name,
                        'wallet_address' => $user->wallet_address,
                    ],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature - wallet ownership verification failed',
                ], 401);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verification failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify Ethereum signature
     */
    private function verifySignature($message, $signature, $address)
    {
        try {
            // Hash the message
            $messageHash = $this->hashMessage($message);

            // Recover the address from signature
            $recoveredAddress = $this->recoverAddress($messageHash, $signature);

            if (! $recoveredAddress) {
                return false;
            }

            // Compare addresses (case-insensitive)
            $result = strtolower($recoveredAddress) === strtolower($address);

            return $result;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Hash the message in Ethereum format
     */
    private function hashMessage($message)
    {
        $prefix  = "\x19Ethereum Signed Message:\n" . strlen($message);
        $message = $prefix . $message;
        return Keccak::hash($message, 256);
    }

    /**
     * Recover address from signature using simplito/elliptic-php
     */
    private function recoverAddress($messageHash, $signature)
    {
        try {
            // Remove '0x' prefix if present
            $signature = str_replace('0x', '', $signature);

            // Split signature into r, s, v
            $r = substr($signature, 0, 64);
            $s = substr($signature, 64, 64);
            $v = hexdec(substr($signature, 128, 2));

            // Adjust v value for Ethereum
            if ($v < 27) {
                $v += 27;
            }

            $recoveryParam = $v - 27;

            // Use elliptic-php library to recover public key
            $ec = new EC('secp256k1');

            // Recover public key
            $publicKey = $ec->recoverPubKey($messageHash, [
                'r' => $r,
                's' => $s,
            ], $recoveryParam);

            // Convert public key to address
            $publicKeyBytes = hex2bin($publicKey->encode('hex'));

            // Remove the prefix byte (0x04 for uncompressed)
            $publicKeyBytes = substr($publicKeyBytes, 1);

            // Keccak-256 hash of public key
            $hash = Keccak::hash($publicKeyBytes, 256);

            // Take last 20 bytes (40 characters) as address
            $address = '0x' . substr($hash, -40);

            return $address;

        } catch (\Exception $e) {
            return false;
        }
    }
}
