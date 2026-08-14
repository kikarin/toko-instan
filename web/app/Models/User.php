<?php

namespace App\Models;

use App\Mail\VerifyEmailMail;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'username',
        'email',
        'bio',
        'phone',
        'phone_verified_at',
        'gender',
        'birth_date',
        'password',
        'role',
        'firebase_uid',
        'avatar',
        'auth_provider',
        'store_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function sendEmailVerificationNotification(): void
    {
        $verifyUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $this->getKey(),
                'hash' => sha1($this->getEmailForVerification()),
            ]
        );

        Mail::to($this->getEmailForVerification())->queue(new VerifyEmailMail($verifyUrl));
    }

    /**
     * @return HasMany<Tenant, $this>
     */
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    /**
     * @return HasOne<Tenant, $this>
     */
    public function primaryTenant(): HasOne
    {
        return $this->hasOne(Tenant::class)->latestOfMany();
    }

    /**
     * @return HasOneThrough<Store, Tenant, $this>
     */
    public function store(): HasOneThrough
    {
        return $this->hasOneThrough(Store::class, Tenant::class, 'user_id', 'tenant_id', 'id', 'id');
    }

    /**
     * @return BelongsTo<Store, $this>
     */
    public function customerStore(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_email', 'email');
    }

    /**
     * @return HasMany<Address, $this>
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Wishlist products (many-to-many through Wishlist pivot model).
     *
     * @return BelongsToMany<Product, $this>
     */
    public function wishlistProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'wishlists')
            ->withTimestamps()
            ->orderBy('wishlists.created_at', 'desc');
    }

    public function isSeller(): bool
    {
        return $this->role === 'seller';
    }

    public function isBuyer(): bool
    {
        return $this->role === 'buyer';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function homePath(): string
    {
        if ($this->role === 'buyer') {
            $storeSlug = $this->customerStore?->slug ?? '';

            return '/'.$storeSlug;
        }

        return match ($this->role) {
            'admin' => '/admin',
            default => '/dashboard',
        };
    }
}
