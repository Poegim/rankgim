<?php

namespace App\Livewire\Settings;

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Profile settings')]
class Profile extends Component
{
    use ProfileValidationRules, WithFileUploads;

    public string $name        = '';
    public string $email       = '';
    public string $countryCode = '';
    public string $city        = '';
    public string $bio         = '';
    public ?float $lat         = null;
    public ?float $lng         = null;

    public string $citySearch      = '';
    public array  $citySuggestions = [];
    public bool   $citySelected    = false;

    #[Validate('nullable|image|max:1024')]
    public $photo = null;

    public function mount(): void
    {
        $user = Auth::user();

        $this->name        = $user->name;
        $this->email       = $user->email;
        $this->countryCode = $user->country_code ?? '';
        $this->city        = $user->city ?? '';
        $this->bio         = $user->bio ?? '';
        $this->lat         = $user->lat ? (float) $user->lat : null;
        $this->lng         = $user->lng ? (float) $user->lng : null;
        $this->citySearch  = $user->city ?? '';
        $this->citySelected = (bool) $user->city;
    }

    public function searchCity(): void
    {
        $this->citySelected    = false;
        $this->citySuggestions = [];

        $query = trim($this->citySearch);

        if (strlen($query) < 2) {
            return;
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Rankgim/1.0',
            ])->timeout(3)->get('https://photon.komoot.io/api/', [
                'q'    => $query,
                'limit' => 6,
                'lang'  => 'en',
            ]);

            if ($response->successful()) {
                $this->citySuggestions = collect($response->json()['features'] ?? [])
                    ->filter(fn($f) => in_array($f['properties']['type'] ?? '', [
                        'city', 'town', 'village', 'municipality',
                    ]))
                    ->take(5)
                    ->map(fn($f) => [
                        'label'   => $this->buildLabel($f['properties']),
                        'lat'     => (float) $f['geometry']['coordinates'][1],
                        'lng'     => (float) $f['geometry']['coordinates'][0],
                        'country' => strtoupper($f['properties']['countrycode'] ?? ''),                    ])
                    ->values()
                    ->all();
            }
        } catch (\Throwable) {
            $this->citySuggestions = [];
        }

    }

    private function buildLabel(array $props): string
    {
        $city    = $props['name'] ?? null;
        $country = $props['country'] ?? null;
        $state   = $props['state'] ?? null;

        if ($city && $country) {
            return $state ? "{$city}, {$state}, {$country}" : "{$city}, {$country}";
        }

        return $city ?? '';
    }

public function selectCity(int $index): void
{
    $suggestion = $this->citySuggestions[$index] ?? null;

    if (! $suggestion) {
        return;
    }

    $this->city       = $suggestion['label'];
    $this->citySearch = $suggestion['label'];
    $this->lat        = $suggestion['lat'];
    $this->lng        = $suggestion['lng'];

    if (! $this->countryCode && $suggestion['country']) {
        $this->countryCode = strtoupper($suggestion['country']);
    }

    $this->citySuggestions = [];
    $this->citySelected    = true;

}

    public function clearCity(): void
    {
        $this->city            = '';
        $this->citySearch      = '';
        $this->lat             = null;
        $this->lng             = null;
        $this->citySelected    = false;
        $this->citySuggestions = [];
    }

    public function removePhoto(): void
    {
        $user = Auth::user();

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
            $user->update(['profile_photo_path' => null]);
        }
    }

    public function updatedPhoto(): void
    {
        $this->validateOnly('photo');

        $user = Auth::user();

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $user->profile_photo_path = $this->photo->store('profile-photos', 'public');
        $user->save();

        $this->photo = null;
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate(array_merge(
            $this->profileRules($user->id),
            [
                'countryCode' => 'nullable|string|size:2',
                'city'        => 'nullable|string|max:150',
                'bio'         => 'nullable|string|max:280',
                'lat'         => 'nullable|numeric|between:-90,90',
                'lng'         => 'nullable|numeric|between:-180,180',
            ]
        ));

        $user->name         = $validated['name'];
        $user->email        = $validated['email'];
        $user->country_code = $validated['countryCode'] ?: null;
        $user->city         = $validated['city'] ?: null;
        $user->bio          = $validated['bio'] ?: null;
        $user->lat          = $validated['lat'];
        $user->lng          = $validated['lng'];

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($this->photo) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $user->profile_photo_path = $this->photo->store('profile-photos', 'public');
            $this->photo = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));
            return;
        }

        $user->sendEmailVerificationNotification();
        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! config('app.demo_mode', false);
    }

    #[Computed]
    public function countries(): array
    {
        return collect(config('countries'))
            ->sortBy('name')
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.settings.profile');
    }
}